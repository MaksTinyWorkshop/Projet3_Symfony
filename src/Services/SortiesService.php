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
use App\Form\SortieFilterForm;
use App\Repository\SortieRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class SortiesService
{
    ////////////////////////////////////// les variables
    private $sortieRepository;
    private $sortieFilterForm;
    private $secu;
    private $entityManager;

    ////////////////////////////////////// constructeur
    public function __construct(Security $secu, SortieRepository $sortieRepository, SortieFilterForm $sortieFilterForm, EntityManagerInterface $entityManager)
    {
        $this->sortieRepository = $sortieRepository;
        $this->sortieFilterForm = $sortieFilterForm;
        $this->secu = $secu;
        $this->entityManager = $entityManager;
    }

    ////////////////////////////////////// les fonctions

    public function showOld()   //recherche des sorties archivées
    {
        $sorties = $this->sortieRepository->findBy([ 'etat' => 5 ]);
        return $sorties;
    }


    public function makeFilter($form){                                           /// I /// Recherche avec les filtres (ou pas)

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
                $check4 = true;
            }else{
                $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));

                $queryBuilder2 = $this->sortieRepository->createQueryBuilder('ss')
                    ->andWhere('ss.organisateur = :userId')
                    ->setParameter('userId', $user)
                    ->andWhere($queryBuilder->expr()->in('ss.etat', 1));
            }
            $baseFiltered = true;
        }

        // si on a activé aucun filtre (au premier chargement par exemple) on joue le filtrage par défaut en fonction des états
        if (!$baseFiltered){
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

        }else if ($baseFiltered && $check4){
            //résultat des requêtes
            $Query1 = $queryBuilder->getQuery()->getResult();
            return $Query1;

        }else{
            //résultat des requêtes
            $Query1 = $queryBuilder->getQuery()->getResult();
            $Query2 = $queryBuilder2->getQuery()->getResult();

            //combinaison des deux requêtes
            $allRequests = array_merge($Query1, $Query2);
            return $allRequests;
        }
    }
    public function changeState($sortieId, $state){
        $sortie = $this->entityManager->getRepository(Sortie::class)->findOneBy([ 'id' => $sortieId ]);
        $etat = $this->entityManager->getRepository(Etat::class)->findOneBy([ 'id' => $state ]);
        if (!$sortie) {
            throw new \Exception("La sortie n'existe pas");
        }
        $sortie->setEtat($etat);
        $this->entityManager->persist($sortie);         // inscription dans la base
        $this->entityManager->flush();                  // flush
    }
    public function delete($sortieId){
        $sortie = $this->entityManager->getRepository(Sortie::class)->findOneBy([ 'id' => $sortieId ]);
        if (!$sortie) {
            throw new \Exception("La sortie n'existe pas");
        }

        $inscriptions = $this->entityManager->getRepository(Inscriptions::class)->findBy([ 'sortie' => $sortieId ]);
        foreach ($inscriptions as $inscription) {
            $this->entityManager->remove($inscription);
        }

        $this->entityManager->remove($sortie);          // inscription dans la base
        $this->entityManager->flush();                  // flush
    }
}