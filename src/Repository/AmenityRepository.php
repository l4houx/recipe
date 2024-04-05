<?php

namespace App\Repository;

use App\Entity\Amenity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Amenity>
 *
 * @method Amenity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Amenity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Amenity[]    findAll()
 * @method Amenity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AmenityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Amenity::class);
    }

    /**
     * Returns the amenities after applying the specified search criterias.
     *
     * @param bool   $isOnline
     * @param string $keyword
     * @param string $slug
     * @param int    $limit
     * @param string $order
     * @param string $sort
     *
     * @return QueryBuilder<Amenity>
     */
    public function getAmenities($isOnline, $keyword, $slug, $limit, $sort, $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a');

        if ('all' !== $isOnline) {
            $qb->andWhere('a.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('a.name LIKE :keyword or :keyword LIKE a.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere('a.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy($sort, $order);

        return $qb;
    }
}
