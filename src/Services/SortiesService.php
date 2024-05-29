<?php

//////////////////// SORTIE SERVICE /////////////////////
///                                                   ///
/// Sert à gérer les différents traitement liés au    ///
/// ServiceController.                                ///
///                                                   ///
/////////////////////////////////////////////////////////


namespace App\Services;



use App\Entity\Sortie;
use App\Form\SortieFilterForm;
use App\Repository\SortieRepository;
use http\Client\Curl\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

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
                $user = $this->secu->getUser();
                if ($user) {
                    $user = $user->getUserIdentifier();
                    $queryBuilder->join('s.organisateur', 'p');
                    $queryBuilder->andWhere('p.email = :pseudo')
                                 ->setParameter('pseudo', $user);
                }else{
                    print (" !!!!!!!!!!!! personne n'est connecté !!!!!!!!!!!!!!!!!! ");
                }
            }

            if ($data['checkbox2']) {
                $user = $this->secu->getUser();
                if ($user) {
                    $user = $user->getId();
                    print($user);

                    $queryBuilder->join('s.sortiesIdSorties', 'i')
                        ->andWhere('i.participantsIdParticipants = :userId')
                        ->setParameter('userId', $user);

                }else{
                    print (" !!!!!!!!!!!! personne n'est connecté !!!!!!!!!!!!!!!!!! ");
                }
            }

            if ($data['checkbox3']) {
                $user = $this->secu->getUser();
                if ($user) {
                    $user = $user->getId();
                    print($user);

                    $queryBuilder->join('s.sortiesIdSorties', 'i')
                        ->andWhere('i.participantsIdParticipants != :userId')
                        ->setParameter('userId', $user);

                }else{
                    print (" !!!!!!!!!!!! personne n'est connecté !!!!!!!!!!!!!!!!!! ");
                }
            }

            if ($data['checkbox4']) {                                                   //pour consulter dans les archives
                $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', 5));
            }else{
                $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));
            }
            $baseFiltered = true;
        }

        // si on est pas passé par le formulaire (au premier chargement par exemple) on joue le filtrage par défaut
        if (!$baseFiltered){
            $queryBuilder->andWhere($queryBuilder->expr()->in('s.etat', [2, 3, 4, 6]));
        }

        return $queryBuilder->getQuery()->getResult();
    }
}