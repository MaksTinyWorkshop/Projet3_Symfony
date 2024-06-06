<?php

namespace App\EventListener;
;

use App\Entity\Sortie;
use App\Repository\EtatRepository;
use DateInterval;
use DateTime;

class CheckStatut
{
    public function __construct(
        private readonly EtatRepository $etatRep
    ){}

    public function checkAndChange(Sortie $sortie):void
    {
        $dateTime = new DateTime();
        // I. // check pour la date de début de l'event, bascule en "Activité en cours, 4"

        if ($dateTime <= $sortie->getDateHeureDebut() && $sortie->getEtat()->getId() == 3) {
            $sortie->setEtat($this->etatRep->findOneBy(['id' => '4']));
        }

        // II. // check pour la date limite d'inscription à l'event, bascule en "Cloturé 3"

        if ($dateTime <= $sortie->getDateLimiteInscription() && $sortie->getEtat()->getId() == 2) {
            $sortie->setEtat($this->etatRep->findOneBy(['id' => '3']));
        }

        // III. // check pour la date de fin de l'event, bascule en "Passé 5"
        $soustrHeures = $sortie->getDuree();
        $soustrSecondes = $soustrHeures * 3600; //Conversion heures en secondes (à cause de Maxime !!!!!)

        $dateCompare = clone $dateTime;
        $dateCompare->sub(new DateInterval('PT' . $soustrSecondes . 'S')); //soustraction de la durée à l'heure de démarrage de l'event

        if ($sortie->getDateHeureDebut() < $dateCompare) {
            $sortie->setEtat($this->etatRep->findOneBy(['id' => '5']));
        }
    }

}