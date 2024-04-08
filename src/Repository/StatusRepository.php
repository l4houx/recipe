<?php

namespace App\Repository;

use App\Entity\Status;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Status>
 *
 * @method Status|null find($id, $lockMode = null, $lockVersion = null)
 * @method Status|null findOneBy(array $criteria, array $orderBy = null)
 * @method Status[]    findAll()
 * @method Status[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    /**
     * Returns the status after applying the specified search criterias.
     *
     * @param string                  $keyword
     * @param int                     $id
     * @param int                     $limit
     * @param string                  $order
     * @param string                  $sort
     *
     * @return QueryBuilder<Status> (StatusController)
     */
    public function getStatus($keyword, $id, $limit, $order, $sort): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s');

        $qb->select('s');

        if ('all' !== $keyword) {
            $qb->andWhere('s.name LIKE :keyword or :keyword LIKE s.name or :keyword LIKE s.color or s.color LIKE :keyword')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $id) {
            $qb->andWhere('s.id = :id')->setParameter('id', $id);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ($sort) {
            $qb->orderBy('s.'.$sort, $order);
        }

        return $qb;
    }
}
