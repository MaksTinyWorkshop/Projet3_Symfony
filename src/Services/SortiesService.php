<?php

////////////////// SORTIES SERVICES /////////////////////
///                                                   ///
/// Sert à gérer les différents traitement liés au    ///
/// ServiceController.                                ///
///                                                   ///
/////////////////////////////////////////////////////////


namespace App\Services;

use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManager;

class SortiesService
{
    function showAll() : array
    {
        $SR = SortieRepository::class;
        $sorties = $SR->findAll();
        return $sorties;
    }
}