<?php

namespace App\Repository;

use App\Entity\VenueSeatingPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenueSeatingPlan>
 *
 * @method VenueSeatingPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenueSeatingPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenueSeatingPlan[]    findAll()
 * @method VenueSeatingPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenueSeatingPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenueSeatingPlan::class);
    }

    //    /**
    //     * @return VenueSeatingPlan[] Returns an array of VenueSeatingPlan objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?VenueSeatingPlan
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
