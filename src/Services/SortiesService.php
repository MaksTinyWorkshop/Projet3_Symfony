<?php

//////////////////// SORTIE SERVICE /////////////////////
///                                                   ///
/// Sert à gérer les différents traitement liés au    ///
/// ServiceController.                                ///
///                                                   ///
/////////////////////////////////////////////////////////

namespace App\Services;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\CreaSortieFormType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SortiesService extends AbstractController
{

    ////////////////////////////////////// constructeur
    public function __construct(
        private SortieRepository       $sortieRepository,
        private Security               $secu,
        private LieuRepository         $lieuRepository,
        private EntityManagerInterface $entityManager,
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
    {                                           /// I /// Recherche avec les filtres (ou pas)

        $baseFiltered = false;                                                          //défini si on est passé par le formulaire de filtres ou pas

        $user = $this->secu->getUser();
        if ($user) {
            $user = $user->getId();
        }

        // construction de la requête
        $queryBuilder = $this->sortieRepository->createQueryBuilder('s');

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
                $queryBuilder->andWhere('s.dateLimiteInscription <= :endDate')
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
            } else {
                $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));
            }
            $baseFiltered = true;
        }

        // si on a activé aucun filtre (au premier chargement par exemple) on joue le filtrage par défaut en fonction des états
        if (!$baseFiltered) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));
        }

        return $queryBuilder->getQuery()->getResult();
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