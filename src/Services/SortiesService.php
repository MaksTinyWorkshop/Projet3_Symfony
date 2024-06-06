<?php

//////////////////// SORTIE SERVICE /////////////////////
///                                                   ///
/// Sert à gérer les différents traitement liés au    ///
/// ServiceController.                                ///
///                                                   ///
/////////////////////////////////////////////////////////

namespace App\Services;

use App\Entity\Etat;
use App\Entity\Inscriptions;
use App\Entity\Sortie;
use App\Form\CreaSortieFormType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service des sorties et des inscriptions aux sorties permettant de :
 * - Créer, modifier, annuler une sortie
 * - Modifier ou annuler une sortie -> changer son Etat mais pas supprimer de la base, supprimer les
 * inscriptions s'il y en a et appel au service d'envoi de mail "SendMailService" pour prévenir les eventuels inscrits
 */
class SortiesService extends AbstractController
{

    ////////////////////////////////////// constructeur
    public function __construct(
        private SortieRepository       $sortieRepository,
        private Security               $secu,
        private LieuRepository         $lieuRepository,
        private EntityManagerInterface $entityManager,
        private SendMailService        $sendMailService,
    )
    {
    }

    ////////////////////////////////////// les fonctions

    public function showOld()   //recherche des sorties archivées
    {
        $sorties = $this->sortieRepository->findBy(['etat' => 5]);
        return $sorties;
    }

    public function makeFilter($form)
    {
        /// I /// Recherche avec les filtres (ou pas)
        $baseFiltered = false;                                                          //défini si on est passé par le formulaire de filtres ou pas
        $check4 = false;

        $user = $this->secu->getUser();
        if ($user) {
            $user = $user->getId();
        }
        ///////////////////
        // Première requête, pour les filtres
        $queryBuilder = $this->sortieRepository->createQueryBuilder('s');
        ///////////////////
        // Deuxième requête, pour les sorties dont le user est l'auteur
        $queryBuilder2 = $this->sortieRepository->createQueryBuilder('ss');

        //Application des filtres pour ajouter aux requêtes
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['site']) {                                                        //recherche par liste des sites
                $queryBuilder->andWhere('s.site = :site')
                    ->setParameter('site', $data['site']);
            }

            if (!empty($data['name'])) {                                                //recherche par fragment de noms
                $queryBuilder->andWhere('s.nom LIKE :name')
                    ->setParameter('name', '%' . $data['name'] . '%');
            }

            if ($data['startDate']) {                                                   //par date de début
                $queryBuilder->andWhere('s.dateHeureDebut >= :startDate')
                    ->setParameter('startDate', $data['startDate']);
            }

            if ($data['endDate']) {                                                     //par date de fin
                $queryBuilder->andWhere('s.dateHeureDebut <= :endDate')
                    ->setParameter('endDate', $data['endDate']);
            }

            if ($data['checkbox1']) {                                                   //par organisateur (soi-même)
                $queryBuilder->join('s.organisateur', 'p');
                $queryBuilder->andWhere('p.id = :userId')
                    ->setParameter('userId', $user);
            }

            if ($data['checkbox2']) {                                                   //par participation (soi-même)
                $queryBuilder->leftJoin('App\Entity\Inscriptions', 'i', 'WITH', 'i.sortie = s.id');
                $queryBuilder->andWhere('i.participant = :userId')
                    ->setParameter('userId', $user);
            }

            if ($data['checkbox3']) {                                                   //par non-participation (soi-même)
                $subQuery = $this->sortieRepository->createQueryBuilder('sq')
                    ->select('1')
                    ->from('App\Entity\Inscriptions', 'j')
                    ->where('j.sortie = s.id')
                    ->andWhere('j.participant = :userId')
                    ->getDQL();

                $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($subQuery)))
                    ->setParameter('userId', $user);
            }

            if ($data['checkbox4']) {                                                   //pour consulter dans les archives
                $queryBuilder->andWhere($queryBuilder->expr()->eq('s.etat', 5));
                $check4 = true;
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));

                $queryBuilder2 = $this->sortieRepository->createQueryBuilder('ss')
                    ->andWhere('ss.organisateur = :userId')
                    ->setParameter('userId', $user)
                    ->andWhere($queryBuilder->expr()->in('ss.etat', 1));
            }
            $baseFiltered = true;
        }

        // si on a activé aucun filtre (au premier chargement par exemple) on joue le filtrage par défaut en fonction des états
        if (!$baseFiltered) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));

            $queryBuilder2->andWhere('ss.organisateur = :userId')
                ->setParameter('userId', $user)
                ->andWhere($queryBuilder->expr()->in('ss.etat', 1));

            //résultat des requêtes
            $Query1 = $queryBuilder->getQuery()->getResult();
            $Query2 = $queryBuilder2->getQuery()->getResult();

            //combinaison des deux requêtes
            $allRequests = array_merge($Query1, $Query2);
            return $allRequests;

        } else if ($baseFiltered && $check4) {
            //résultat des requêtes
            $Query1 = $queryBuilder->getQuery()->getResult();
            return $Query1;

        } else {
            //résultat des requêtes
            $Query1 = $queryBuilder->getQuery()->getResult();
            $Query2 = $queryBuilder2->getQuery()->getResult();

            //combinaison des deux requêtes
            $allRequests = array_merge($Query1, $Query2);
            return $allRequests;
        }
    }

    public function changeState($sortieId, $state): void
    {
        $sortie = $this->entityManager->getRepository(Sortie::class)->findOneBy(['id' => $sortieId]);
        $etat = $this->entityManager->getRepository(Etat::class)->findOneBy(['id' => $state]);
        if (!$sortie) {
            throw new \Exception("La sortie n'existe pas");
        }
        $sortie->setEtat($etat);
        $this->entityManager->persist($sortie);         // inscription dans la base
        $this->entityManager->flush();                  // flush
    }

    public function delete(Request $request, $sortieId)
    {
        $sortie = $this->entityManager->getRepository(Sortie::class)->findOneBy(['id' => $sortieId]);
        if (!$sortie) {
            throw new \Exception("La sortie n'existe pas");
        }
        $etat = $sortie->getEtat()->getId();

        switch ($etat) {
            // La sortie est crée mais non publiée
            case 1:
                $this->entityManager->remove($sortie);          // inscription dans la base
                $this->entityManager->flush();
                $this->addFlash('success', 'Sortie supprimée');// flush
                break;
            // La sortie est crée et publiée
            case 2:
                // Si le user en session est l'administrateur
                if ($this->secu->getUser()->getRoles()[0] === 'ROLE_ADMIN') {
                    $motifAnnulation = 'Sortie annulée par l\'administrateur';
                } else {
                    // Sinon récupérer un motif par l'utilisateur (formulaire modale)
                    $motifAnnulation = $request->request->get('motifAnnulation');
                    // Vérifiez si le motif d'annulation est vide
                    if (empty($request->request->get('motifAnnulation'))) {
                        // Ajoutez un message d'erreur
                        $this->addFlash('danger', 'Veuillez saisir un motif d\'annulation.');
                        // Redirigez l'utilisateur vers la page de modification de la sortie
                        return $this->redirectToRoute('sortie_modifier', ['pseudo' => $this->secu->getUser()->getPseudo(), 'sortieId' => $sortieId]);
                    }

                }
                $etatAnnuleId = 6;
                $etatAnnule = $this->entityManager->getRepository(Etat::class)->find($etatAnnuleId);
                $sortie->setInfosSortie($motifAnnulation);
                $sortie->setEtat($etatAnnule);

                $inscriptions = $this->entityManager->getRepository(Inscriptions::class)->findBy(['sortie' => $sortieId]);

                ////////////////////////////////////////////////////////////////////////////
                ///////////// Feature d'envoi de mail désactivée temporairement ////////////
                ///////////// (trop d'envoi de mail à la fois pour la version   ////////////
                ///////////// gratuite de MailTrap)                             ////////////
                /// ////////////////////////////////////////////////////////////////////////
                /*
                $participants = $this->entityManager->getRepository(Inscriptions::class)->getParticipantsBySortieId($sortieId);
                foreach ($participants as $participant) {
                    $context = compact('sortie', 'participant');
                    $this->sendMailService->sendMail(
                        'cancelledSortie@sortir.com',
                        $participant['email'],
                        'Annulation de sortie',
                        'email/cancelled_sortie.html.twig',
                        $context
                    );
                }
                */

                foreach ($inscriptions as $inscription) {
                    $this->entityManager->remove($inscription);
                };
                $this->entityManager->persist($sortie);
                $this->entityManager->flush();
                $this->addFlash('success', 'Sortie '. $sortie->getNom().' correctement annulée, participants prévenus');
                break;
            default:
                $this->addFlash('danger', 'Déso mon coco tu peux pas supprimer ça ');
                break;
        }

    }

    public function creerSortie(Request $request): Response
    {
        $sortie = new Sortie();
        $organisateur = $this->secu->getUser();
        $form = $this->createForm(CreaSortieFormType::class, $sortie);

        return $this->creerOuModifierUneSortie($request, $form, $sortie, $organisateur, false);

    }

    public function modifierUneSortie(Request $request, string $id): Response
    {
        $sortie = $this->entityManager->getRepository(Sortie::class)->find($id);
        $organisateur = $sortie->getOrganisateur();
        $form = $this->createForm(CreaSortieFormType::class, $sortie, ['is_edit' => true]);

        return $this->creerOuModifierUneSortie($request, $form, $sortie, $organisateur, true);
    }

    public function checkStatus(\DateTime $dateTime): void
    {
        // I. // check pour la date de début de l'event, bascule en "Activité en cours, 4"
        $sorties = $this->sortieRepository->createQueryBuilder('s')
            ->where('s.dateHeureDebut <= :dateTime')
            ->andWhere('s.etat = 3')
            ->setParameter('dateTime', $dateTime)
            ->getQuery()
            ->getResult();

        foreach ($sorties as $sortie) {
            $sortie->setEtat($this->entityManager->getRepository(Etat::class)->findOneBy(['id' => '4']));
            $this->entityManager->persist($sortie);
        }

        // II. // check pour la date limite d'inscription à l'event, bascule en "Cloturé 3"
        $sortieInscr = $this->sortieRepository->createQueryBuilder('si')
            ->where('si.dateLimiteInscription <= :dateTime')
            ->andWhere('si.etat = 2')
            ->setParameter('dateTime', $dateTime)
            ->getQuery()
            ->getResult();

        foreach ($sortieInscr as $sortieIn) {
            $sortieIn->setEtat($this->entityManager->getRepository(Etat::class)->findOneBy(['id' => '3']));
            //$this->entityManager->persist($sortieIn);
        }
        $this->entityManager->flush();

        // III. // check pour la date de fin de l'event, bascule en "Passé 5"
        $sortieFin = $this->sortieRepository->createQueryBuilder('sf')
            ->Where('sf.etat = 4')
            ->getQuery()
            ->getResult();
        foreach ($sortieFin as $sortieF) {
            $soustrHeures = $sortieF->getDuree();
            $soustrSecondes = $soustrHeures * 3600; //Conversion heures en secondes (à cause de Maxime !!!!!)

            $dateCompare = clone $dateTime;
            $dateCompare->sub(new DateInterval('PT' . $soustrSecondes . 'S')); //soustraction de la durée à l'heure de démarrage de l'event

            if ($sortieF->getDateHeureDebut() < $dateCompare) {
                $sortieF->setEtat($this->entityManager->getRepository(Etat::class)->findOneBy(['id' => '5']));
                //$this->entityManager->persist($sortieF);
            }
        }
        $this->entityManager->flush();
    }

    ///////////////////////////////// Fonctions privées

    /**
     * Fonction privée qui évite la répétition et qui
     * transforme les données de lieux en JSON pour le remplissage
     * dynamique des champs via Javascript
     */
    private function getLieuxData(): bool|string
    {
        $lieux = $this->lieuRepository->findAll();
        $lieuxData = [];
        foreach ($lieux as $lieu) {
            $lieuxData[] = [
                'id' => $lieu->getId(),
                'ville' => $lieu->getVille(),
                'rue' => $lieu->getRue(),
                'codePostal' => $lieu->getCodePostal()
            ];
        }
        return $lieuxData = json_encode($lieuxData);
    }

    /**
     * Fonction privée qui regroupe les traits communs de créer ou modifier une Sortie
     */
    private function creerOuModifierUneSortie(Request $request, $form, $sortie, $organisateur, $isEdit): Response
    {
        $lieux = $this->getLieuxData();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('enregistrer')->isClicked()) {
                $etat = $this->entityManager->getRepository(Etat::class)->findOneBy(['id' => '1']);
                if ($sortie->getEtat() && $sortie->getEtat()->getId() == 2) {
                    $this->addFlash('warning', 'La sortie est déjà publiée, cliquez sur "publier"!');
                    return $this->redirectToRoute('sortie_modifier', ['pseudo' => $organisateur->getPseudo(),'sortieId' => $sortie->getId()]);
                }
            } elseif ($form->get('publier')->isClicked()) {
                $etat = $this->entityManager->getRepository(Etat::class)->findOneBy(['id' => '2']);
            }

            if (isset($etat)) {
                $sortie->setEtat($etat);
            }

            $sortie->setOrganisateur($organisateur)
                ->setSite($organisateur->getSite());

            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            $flashMessage = $sortie->getEtat()->getId() == 1
                ? 'Sortie ' . $sortie->getNom() . ' correctement enregistrée'
                : 'Sortie ' . $sortie->getNom() . ' correctement publiée';

            $this->addFlash('success', $flashMessage);

            return $this->redirectToRoute('sortie_main');
        }

        return $this->render('sortie/formulaireSortie.html.twig', [
            'sortieForm' => $form->createView(),
            'is_edit' => $isEdit,
            'organisateur' => $organisateur,
            'lieux' => $lieux,
            'sortie' => $sortie,
        ]);
    }
}