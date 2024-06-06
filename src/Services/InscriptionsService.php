<?php

//////////////////// INSCRIPTIONS SERVICE /////////////////////
///                                                    ///
/// Sert à gérer les différents traitement liés aux    ///
/// inscriptions                                       ///
///                                                    ///
/////////////////////////////////////////////////////////

namespace App\Services;

use App\Entity\Inscriptions;
use App\Entity\Participants;
use App\Entity\Sortie;
use App\Repository\InscriptionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\AbstractList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

class InscriptionsService extends AbstractController
{
    ////////////////////////////////////// les variables
    private $inscriptionRepository;
    private $secu;
    private $entityManager;


    ////////////////////////////////////// constructeur
    public function __construct(InscriptionsRepository $inscriptionRepository, Security $secu, EntityManagerInterface $entityManager)
    {
        $this->inscriptionRepository = $inscriptionRepository;
        $this->secu = $secu;
        $this->entityManager = $entityManager;
    }

    ////////////////////////////////////// les fonctions

    public function showAll()   //recherche des sorties archivées
    {
        $inscriptions = $this->inscriptionRepository->findAll();
        return $inscriptions;
    }
    public function deleteOne($sortieId)
    {
        $userId = $this->secu->getUser();
        if ($userId) {
            $userId = $userId->getId();
        }

        // construction de la requête
        $queryBuilder = $this->inscriptionRepository->createQueryBuilder('i')
                                                    ->andWhere('i.sortie = :sortieId')
                                                    ->setParameter(':sortieId', $sortieId)
                                                    ->andWhere('i.participant = :userId')
                                                    ->setParameter('userId', $userId);
        $inscription = $queryBuilder->getQuery()->getOneOrNullResult();

        if($inscription != null) {

            $this->entityManager->remove($inscription);
            $this->entityManager->flush();
        }
    }
    public function addOne($sortieId)
    {
        $userId = $this->secu->getUser();
        if ($userId) {
            $userId = $userId->getId();
        }
        // Récup de l'event
        $sortie = $this->entityManager->getRepository(Sortie::class)->find($sortieId);
        if (!$sortie) {
            throw new \Exception("La sortie n'existe pas");
        }
        // Récup du participant
        $participant = $this->entityManager->getRepository(Participants::class)->find($userId);
        if (!$participant) {
            throw new \Exception("L'utilisateur n'existe pas");
        }

        //vérification d'insciption à la même date pour un autre event
        $ListeDesInscriptions = $this->inscriptionRepository->createQueryBuilder('ir')
            ->andWhere('ir.sortie != :sortieId')
            ->setParameter(':sortieId', $sortieId)
            ->andWhere('ir.participant = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        // Boucle de vérification sur toutes les inscriptions de l'utilisateur
        foreach ($ListeDesInscriptions as $item) {
            $dateEventCourant = $item->getSortie()->getDateHeureDebut()->format('Y-m-d'); // Conversion de format pour avoir juste la date
            $dateEventToCheck = $sortie->getDateHeureDebut()->format('Y-m-d'); // Conversion de format pour avoir juste la date

            if ($dateEventCourant == $dateEventToCheck) { // Si les dates coïncident
                $this->addFlash('danger', "Pour cette date, vous êtes déjà inscrit à un autre événement."); // Message d'erreur
                return; // on dégage de la fonction
            }
        }
        // Sinon ça passe, on ajoute l'inscription
        $inscription = new Inscriptions();
        $inscription->setSortie($sortie);
        $inscription->setParticipant($participant);
        $this->entityManager->persist($inscription); // Inscription dans la base (sans déc !)
        $this->entityManager->flush();               // Flush (pas l'oublier ce connard !!!)
    }
}