<?php

namespace App\Repository;

use App\Entity\VenueSeatingPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenueSeatingPlan>
 *
 * @method VenueSeatingPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenueSeatingPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenueSeatingPlan[]    findAll()
 * @method VenueSeatingPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenueSeatingPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenueSeatingPlan::class);
    }

    public function getVenuesSeatingPlans($id, $venue, $restaurant, $slug, $limit, $count): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s');

        if ($count) {
            $qb->select('COUNT(s)');
        } else {
            $qb->select('s');
        }

        if ('all' !== $id) {
            $qb->andWhere('s.id = :id')->setParameter('id', $id);
        }

        if ('all' !== $slug) {
            $qb->andWhere('s.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $venue || 'all' !== $restaurant) {
            $qb->join('s.venue', 'venue');
        }

        if ('all' !== $venue) {
            $qb->andWhere('venue.slug = :venue')->setParameter('venue', $venue);
        }

        if ('all' !== $restaurant) {
            $qb->join('venue.restaurant', 'restaurant');
            $qb->andWhere('restaurant.slug = :restaurant')->setParameter('restaurant', $restaurant);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy('s.id', 'DESC');

        return $qb;
    }
}
