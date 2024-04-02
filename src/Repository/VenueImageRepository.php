<?php

namespace App\Repository;

use App\Entity\VenueImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenueImage>
 *
 * @method VenueImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenueImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenueImage[]    findAll()
 * @method VenueImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenueImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenueImage::class);
    }

    //    /**
    //     * @return VenueImage[] Returns an array of VenueImage objects
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

    //    public function findOneBySomeField($value): ?VenueImage
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
