<?php

namespace App\Repository;

use App\Entity\VenueType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenueType>
 *
 * @method VenueType|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenueType|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenueType[]    findAll()
 * @method VenueType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenueTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenueType::class);
    }

    public function getVenuesTypes($isOnline, $keyword, $slug, $limit, $sort, $order, $hasvenues): QueryBuilder
    {
        $qb = $this->createQueryBuilder('v');
        $qb->select('v');
        $qb->addSelect('COUNT(v) as HIDDEN venuescount');

        if ('all' !== $isOnline) {
            $qb->andWhere('v.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('v.name LIKE :keyword or :keyword LIKE v.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere('v.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (true === $hasvenues || 1 === $hasvenues) {
            $qb->join('v.venues', 'venues');
        }

        // $qb->orderBy($sort, $order);

        $qb->groupBy('v');

        return $qb;
    }
}
