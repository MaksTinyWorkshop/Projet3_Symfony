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
use Symfony\Bundle\SecurityBundle\Security;

class InscriptionsService
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

        // nouvelle inscription
        $inscription = new Inscriptions();
        $inscription->setSortie($sortie);
        $inscription->setParticipant($participant);
        $this->entityManager->persist($inscription);    // inscription dans la base
        $this->entityManager->flush();                  // flush
    }
}