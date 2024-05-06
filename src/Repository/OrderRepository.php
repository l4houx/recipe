<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function getOrders($status, $user, $restaurant, $recipe, $recipeDate, $recipeSubscription, $reference, $upcomingsubscriptions, $datefrom, $dateto, $paymentgateway, $sort, $order, $limit, $count, $ordersQuantityByDateStat, $sumOrderElements): QueryBuilder {
        $qb = $this->createQueryBuilder("o");

        if ($count) {
            $qb->select("COUNT(o)");
        } elseif ($ordersQuantityByDateStat) {
            $qb->select("SUM(orderelement.quantity), DATE(o.createdAt) as dateCreatedAt");
            $qb->groupBy("dateCreatedAt");
        } elseif ($sumOrderElements) {
            $qb->select("SUM(orderelement.quantity)");
        } else {
            $qb->select("o");
        }

        if ($status !== "all") {
            $qb->andWhere("o.status = :status")->setParameter("status", $status);
        }

        if ($user !== "all") {
            $qb->leftJoin("o.user", "user");
            $qb->andWhere("user.slug = :user")->setParameter("user", $user);
        }

        if ($restaurant !== "all" || $upcomingsubscriptions !== "all" || $recipe !== "all" || $recipeDate !== "all" || $recipeSubscription !== "all" || $ordersQuantityByDateStat || $sumOrderElements) {
            $qb->leftJoin("o.orderelements", "orderelement");
            $qb->leftJoin("orderelement.recipesubscription", "recipesubscription");
            $qb->leftJoin("recipesubscription.recipedate", "recipedate");
        }

        if ($restaurant !== "all" || $recipe !== "all") {
            $qb->leftJoin("recipedate.recipe", "recipe");
        }

        if ($restaurant !== "all") {
            $qb->leftJoin("recipe.restaurant", "restaurant");
            $qb->andWhere("restaurant.slug = :restaurant")->setParameter("restaurant", $restaurant);
        }

        if ($recipe !== "all") {
            //$qb->leftJoin("recipe.translations", "recipetranslations");
            $qb->andWhere("recipe.slug = :recipe")->setParameter("recipe", $recipe);
        }

        if ($recipeDate !== "all") {
            $qb->andWhere("recipedate.reference = :recipedate")->setParameter("recipedate", $recipeDate);
        }

        if ($recipeSubscription !== "all") {
            $qb->andWhere("recipesubscription.reference = :recipesubscription")->setParameter("recipesubscription", $recipeSubscription);
        }

        if ($reference !== "all") {
            $qb->andWhere("o.reference = :reference")->setParameter("reference", $reference);
        }

        if ($datefrom !== "all") {
            $qb->andWhere("o.createdAt >= :datefrom")->setParameter("datefrom", $datefrom);
        }

        if ($dateto !== "all") {
            $qb->andWhere("o.createdAt <= :dateto")->setParameter("dateto", $dateto);
        }

        if ($paymentgateway !== "all") {
            $qb->leftJoin("o.paymentgateway", "paymentgateway");
            $qb->andWhere("paymentgateway.slug = :paymentgateway")->setParameter("paymentgateway", $paymentgateway);
        }

        if ($upcomingsubscriptions !== "all") {
            if ($upcomingsubscriptions === 1) {
                $qb->andWhere("recipedate.startdate >= NOW()");
            } else if ($upcomingsubscriptions === 0) {
                $qb->andWhere("recipedate.startdate < NOW()");
            }
        }

        if ($limit !== "all") {
            $qb->setMaxResults($limit);
        }

        if ($sort && !$ordersQuantityByDateStat) {
            $qb->orderBy("o." . $sort, $order);
        }

        return $qb;
    }
}
