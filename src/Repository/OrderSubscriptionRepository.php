<?php

namespace App\Repository;

use App\Entity\OrderSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderSubscription>
 *
 * @method OrderSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderSubscription[]    findAll()
 * @method OrderSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderSubscription::class);
    }

//    /**
//     * @return OrderSubscription[] Returns an array of OrderSubscription objects
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

//    public function findOneBySomeField($value): ?OrderSubscription
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
