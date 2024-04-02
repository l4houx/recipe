<?php

namespace App\Repository;

use App\Entity\PaymentGateway;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentGateway>
 *
 * @method PaymentGateway|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentGateway|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentGateway[]    findAll()
 * @method PaymentGateway[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentGatewayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentGateway::class);
    }

    //    /**
    //     * @return PaymentGateway[] Returns an array of PaymentGateway objects
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

    //    public function findOneBySomeField($value): ?PaymentGateway
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
