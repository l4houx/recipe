<?php

namespace App\Repository;

use App\Entity\PayoutRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PayoutRequest>
 *
 * @method PayoutRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method PayoutRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method PayoutRequest[]    findAll()
 * @method PayoutRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PayoutRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayoutRequest::class);
    }

    //    /**
    //     * @return PayoutRequest[] Returns an array of PayoutRequest objects
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

    //    public function findOneBySomeField($value): ?PayoutRequest
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
