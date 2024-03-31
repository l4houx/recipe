<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 *
 * @method Country|null find($id, $lockMode = null, $lockVersion = null)
 * @method Country|null findOneBy(array $criteria, array $orderBy = null)
 * @method Country[]    findAll()
 * @method Country[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    /**
     * Returns the countries after applying the specified search criterias.
     *
     * @param int    $id
     * @param bool   $isOnline
     * @param string $keyword
     * @param string $isocode
     * @param string $slug
     * @param int    $limit
     * @param string $sort
     * @param string $order
     */
    public function getCountries($id, $isOnline, $keyword, $isocode, $slug, $limit, $sort, $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c');

        if ('all' !== $id) {
            $qb->andWhere('c.id = :id')->setParameter('id', $id);
        }

        if ('all' !== $isOnline) {
            $qb->andWhere('c.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('c.name LIKE :keyword or :keyword LIKE c.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $isocode) {
            $qb->andWhere('c.code = :isocode')->setParameter('isocode', $isocode);
        }

        if ('all' !== $slug) {
            $qb->andWhere('c.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy($sort, $order);

        return $qb;
    }
}
