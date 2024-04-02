<?php

namespace App\Repository;

use App\Entity\CartElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartElement>
 *
 * @method CartElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method CartElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method CartElement[]    findAll()
 * @method CartElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartElement::class);
    }

//    /**
//     * @return CartElement[] Returns an array of CartElement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CartElement
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
