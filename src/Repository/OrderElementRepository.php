<?php

namespace App\Repository;

use App\Entity\OrderElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderElement>
 *
 * @method OrderElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderElement[]    findAll()
 * @method OrderElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderElement::class);
    }

    //    /**
    //     * @return OrderElement[] Returns an array of OrderElement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?OrderElement
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
