<?php

namespace App\Repository;

use App\Entity\PaymentToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentToken>
 *
 * @method PaymentToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentToken[]    findAll()
 * @method PaymentToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentToken::class);
    }

    //    /**
    //     * @return PaymentToken[] Returns an array of PaymentToken objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PaymentToken
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
