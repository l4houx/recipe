<?php

namespace App\Repository;

use App\Entity\Scanner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scanner>
 *
 * @method Scanner|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scanner|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scanner[]    findAll()
 * @method Scanner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScannerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scanner::class);
    }

    //    /**
    //     * @return Scanner[] Returns an array of Scanner objects
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

    //    public function findOneBySomeField($value): ?Scanner
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
