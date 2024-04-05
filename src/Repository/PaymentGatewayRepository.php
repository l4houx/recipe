<?php

namespace App\Repository;

use App\Entity\PaymentGateway;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function getPaymentGateways($restaurant, $enabled, $gatewayFactoryName, $slug, $sort, $order, $restaurantPayoutPaypalEnabled, $restaurantPayoutStripeEnabled): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p');

        if (null !== $restaurant) {
            $qb->leftJoin('p.restaurant', 'restaurant');
            $qb->andWhere('restaurant.slug = :restaurant')->setParameter('restaurant', $restaurant);
            if ('no' == $restaurantPayoutPaypalEnabled) {
                $qb->andWhere("p.factoryName != 'paypal_rest'");
            }
            if ('no' == $restaurantPayoutStripeEnabled) {
                $qb->andWhere("p.gatewayName != 'Stripe'");
            }
        } else {
            $qb->andWhere('p.restaurant IS NULL');
        }

        if ('all' !== $enabled) {
            $qb->andWhere('p.enabled = :enabled')->setParameter('enabled', $enabled);
        }

        if ('all' !== $gatewayFactoryName) {
            $qb->andWhere('p.factoryName = :gatewayFactoryName')->setParameter('gatewayFactoryName', $gatewayFactoryName);
        }

        if ('all' !== $slug) {
            $qb->andWhere('p.slug = :slug')->setParameter('slug', $slug);
        }

        $qb->orderBy('p.'.$sort, $order);

        // Always exclude the Point of sale payment gateway (offline) and the payment gateway that handles free orders (orders with total amount = 0)
        $qb->andWhere('p.slug != :pointOfSale')->setParameter('pointOfSale', 'point-of-sale');
        $qb->andWhere('p.slug != :free')->setParameter('free', 'free');

        return $qb;
    }
}
