<?php

namespace App\Repository;

use App\Entity\Level;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Level>
 *
 * @method Level|null find($id, $lockMode = null, $lockVersion = null)
 * @method Level|null findOneBy(array $criteria, array $orderBy = null)
 * @method Level[]    findAll()
 * @method Level[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LevelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Level::class);
    }

    /**
     * Returns the levels after applying the specified search criterias.
     *
     * @param string                  $keyword
     * @param int                     $id
     * @param int                     $limit
     * @param string                  $order
     * @param string                  $sort
     *
     * @return QueryBuilder<Level> (LevelController)
     */
    public function getLevels($keyword, $id, $limit, $order, $sort): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');

        $qb->select('l');

        if ('all' !== $keyword) {
            $qb->andWhere('l.name LIKE :keyword or :keyword LIKE l.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $id) {
            $qb->andWhere('l.id = :id')->setParameter('id', $id);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ($sort) {
            $qb->orderBy('l.'.$sort, $order);
        }

        return $qb;
    }
}
