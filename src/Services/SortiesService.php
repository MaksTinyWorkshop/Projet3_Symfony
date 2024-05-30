<?php

//////////////////// SORTIE SERVICE /////////////////////
///                                                   ///
/// Sert à gérer les différents traitement liés au    ///
/// ServiceController.                                ///
///                                                   ///
/////////////////////////////////////////////////////////

namespace App\Services;

use App\Entity\Inscriptions;
use App\Form\SortieFilterForm;
use App\Repository\SortieRepository;
use Symfony\Bundle\SecurityBundle\Security;

class SortiesService
{
    ////////////////////////////////////// les variables
    private $sortieRepository;
    private $sortieFilterForm;
    private $secu;

    ////////////////////////////////////// constructeur
    public function __construct(Security $secu, SortieRepository $sortieRepository, SortieFilterForm $sortieFilterForm)
    {
        $this->sortieRepository = $sortieRepository;
        $this->sortieFilterForm = $sortieFilterForm;
        $this->secu = $secu;
    }

    ////////////////////////////////////// les fonctions

    public function showOld()   //recherche des sorties archivées
    {
        $sorties = $this->sortieRepository->findBy([ 'etat' => 5 ]);
        return $sorties;
    }


    public function makeFilter($form){                                           /// I /// Recherche avec les filtres (ou pas)

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
            }else{
                $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));
            }
            $baseFiltered = true;
        }

        // si on a activé aucun filtre (au premier chargement par exemple) on joue le filtrage par défaut en fonction des états
        if (!$baseFiltered){
            $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));
        }

        return $queryBuilder->getQuery()->getResult();
    }
}