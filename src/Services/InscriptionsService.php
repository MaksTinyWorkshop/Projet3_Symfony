<?php

//////////////////// INSCRIPTIONS SERVICE /////////////////////
///                                                    ///
/// Sert à gérer les différents traitement liés aux    ///
/// inscriptions                                       ///
///                                                    ///
/////////////////////////////////////////////////////////

namespace App\Services;

use App\Repository\InscriptionsRepository;

class InscriptionsService
{
    ////////////////////////////////////// les variables
    private $inscriptionRepository;

    ////////////////////////////////////// constructeur
    public function __construct(InscriptionsRepository $inscriptionRepository)
    {
        $this->inscriptionRepository = $inscriptionRepository;
    }

    ////////////////////////////////////// les fonctions

    public function showAll()   //recherche des sorties archivées
    {
        $inscriptions = $this->inscriptionRepository->findAll();
        return $inscriptions;
    }
}