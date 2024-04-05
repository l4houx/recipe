<?php

namespace App\Repository;

use App\Entity\PayoutRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function getPayoutRequests($reference, $recipedate, $restaurant, $datefrom, $dateto, $status, $sort, $order, $limit, $count): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        if ($count) {
            $qb->select('COUNT(p)');
        } else {
            $qb->select('p');
        }

        if ('all' !== $reference) {
            $qb->andWhere('p.reference = :reference')->setParameter('reference', $reference);
        }

        if ('all' !== $recipedate) {
            $qb->leftJoin('p.recipeDate', 'recipeDate');
            $qb->andWhere('recipeDate.reference = :recipeDate')->setParameter('recipeDate', $recipedate);
        }

        if ('all' !== $restaurant) {
            $qb->leftJoin('p.restaurant', 'restaurant');
            $qb->andWhere('restaurant.slug = :restaurant')->setParameter('restaurant', $restaurant);
        }

        if ('all' !== $datefrom) {
            $qb->andWhere('p.createdAt >= :datefrom')->setParameter('datefrom', $datefrom);
        }

        if ('all' !== $dateto) {
            $qb->andWhere('p.createdAt <= :dateto')->setParameter('dateto', $dateto);
        }

        if ('all' !== $status) {
            $qb->andWhere('p.status = :status')->setParameter('status', $status);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ($sort) {
            $qb->orderBy('p.'.$sort, $order);
        }

        return $qb;
    }
}
