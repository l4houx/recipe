<?php

namespace App\Repository;

use App\Entity\SubscriptionReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubscriptionReservation>
 *
 * @method SubscriptionReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubscriptionReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubscriptionReservation[]    findAll()
 * @method SubscriptionReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionReservation::class);
    }

    //    /**
    //     * @return SubscriptionReservation[] Returns an array of SubscriptionReservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?SubscriptionReservation
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
