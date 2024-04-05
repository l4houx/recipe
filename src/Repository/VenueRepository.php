<?php

namespace App\Repository;

use App\Entity\Venue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Venue>
 *
 * @method Venue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Venue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Venue[]    findAll()
 * @method Venue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venue::class);
    }

    public function getVenues($restaurant, $isOnline, $keyword, $country, $venuetypes, $directory, $slug, $limit, $minseatedguests, $maxseatedguests, $minstandingguests, $maxstandingguests, $count, $restaurantEnabled): QueryBuilder
    {
        $qb = $this->createQueryBuilder('v');

        if ($count) {
            $qb->select('COUNT(v)');
        } else {
            $qb->select('v');
        }

        // if ($restaurant !== "all" || $restaurantEnabled !== "all") {
        if ('all' !== $restaurant) {
            $qb->innerJoin('v.restaurant', 'restaurant');
        }
        if ('all' !== $restaurant) {
            $qb->andWhere('restaurant.slug = :restaurant')->setParameter('restaurant', $restaurant);
        }

        /*if ($restaurantEnabled !== "all") {
            $qb->innerJoin("restaurant.user", "user");
            $qb->andWhere("user.isVerified = :userEnabled")->setParameter("userEnabled", $restaurantEnabled);
        }*/

        if ('all' !== $keyword || 'all' !== $slug) {
            $qb->join('v.translations', 'translations');
        }

        if ('all' !== $country) {
            $qb->join('v.country', 'country');
            $qb->andWhere('country.slug = :country')->setParameter('country', $country);
        }

        if ('all' !== $venuetypes) {
            $qb->join('v.type', 'venuetype');
            $i = 0;
            $orX = $qb->expr()->orX();
            foreach ($venuetypes as $venuetype) {
                $orX->add('venuetype.slug = :venuetypeslug'.$i);
                $qb->setParameter('venuetypeslug'.$i, $venuetype);
                ++$i;
            }
            $qb->andWhere($orX);
        }

        if ('all' !== $isOnline) {
            $qb->andWhere('v.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('v.name LIKE :keyword or :keyword LIKE v.name or v.description LIKE :keyword or :keyword LIKE v.description')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $directory) {
            $qb->andWhere('v.listedondirectory = :listedondirectory')->setParameter('listedondirectory', $directory);
        }

        if ('all' !== $slug) {
            $qb->andWhere('v.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ('all' !== $minseatedguests) {
            $qb->andWhere('v.seatedguests >= :minseatedguests')->setParameter('minseatedguests', $minseatedguests);
        }

        if ('all' !== $maxseatedguests) {
            $qb->andWhere('v.seatedguests <= :maxseatedguests')->setParameter('maxseatedguests', $maxseatedguests);
        }

        if ('all' !== $minstandingguests) {
            $qb->andWhere('v.standingguests >= :minstandingguests')->setParameter('minstandingguests', $minstandingguests);
        }

        if ('all' !== $maxseatedguests) {
            $qb->andWhere('v.standingguests <= :maxstandingguests')->setParameter('maxstandingguests', $maxstandingguests);
        }

        $qb->orderBy('v.createdAt', 'DESC');

        return $qb;
    }
}
