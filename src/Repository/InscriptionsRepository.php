<?php

namespace App\Repository;

use App\Entity\Inscriptions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inscriptions>
 */
class InscriptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscriptions::class);
    }

    public function getParticipantsBySortieId($sortieId)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('p.email, p.pseudo, p.prenom')
            ->join('i.participant', 'p')
            ->where('i.sortie = :sortieId')
            ->setParameter('sortieId', $sortieId);

        return $qb->getQuery()->getResult();
    }

    public function getSortiesByParticipantId($participantId)
    {
        $qb = $this->createQueryBuilder('i')
            ->select('s')
            ->join('i.sortie', 's')
            ->where('i.participant = :participantId')
            ->setParameter('participantId', $participantId);

        return $qb->getQuery()->getResult();
    }
    //    /**
    //     * @return Inscriptions[] Returns an array of Inscriptions objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Inscriptions
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
