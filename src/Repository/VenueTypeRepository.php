<?php

namespace App\Repository;

use App\Entity\VenueType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenueType>
 *
 * @method VenueType|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenueType|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenueType[]    findAll()
 * @method VenueType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenueTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenueType::class);
    }

    //    /**
    //     * @return VenueType[] Returns an array of VenueType objects
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

    //    public function findOneBySomeField($value): ?VenueType
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
