<?php

////////////////// SITE SERVICE /////////////////////////
///                                                   ///
/// Sert à gérer les différents traitement liés au    ///
/// Sites.                                            ///
///                                                   ///
/////////////////////////////////////////////////////////



namespace App\Services;

use App\Repository\SiteRepository;

class SiteService
{
    ////////////////////////////////// les variables
    private $siteRepository;

    ////////////////////////////////// Constructeur
    public function __construct(SiteRepository $siteRepository)
    {
        $this->siteRepository = $siteRepository;
    }

    ////////////////////////////////// Les fonctions
    public function showAll()
    {
        $sites = $this->siteRepository->findAll();
        return $sites;
    }
}