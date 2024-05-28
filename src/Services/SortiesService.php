<?php

//////////////////// SORTIE SERVICE /////////////////////
///                                                   ///
/// Sert à gérer les différents traitement liés au    ///
/// ServiceController.                                ///
///                                                   ///
/////////////////////////////////////////////////////////


namespace App\Services;

use App\Entity\Sortie;
use App\Repository\SortieRepository;
class SortiesService
{
    ////////////////////////////////////// les variables
    private $sortieRepository;

    ////////////////////////////////////// constructeur
    public function __construct(SortieRepository $sortieRepository)
    {
        $this->sortieRepository = $sortieRepository;
    }

    ////////////////////////////////////// les fonctions
    public function showActive()   //recherche de toutes les sorties
    {
        $sorties = $this->sortieRepository->findBy([ 'etat' => [2,3,4,6] ]);
        return $sorties;
    }
    public function showOld()   //recherche des sorties archivées
    {
        $sorties = $this->sortieRepository->findBy([ 'etat' => 5 ]);
        return $sorties;
    }
}