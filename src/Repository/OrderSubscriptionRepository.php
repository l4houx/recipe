<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use App\Entity\OrderSubscription;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<OrderSubscription>
 *
 * @method OrderSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderSubscription[]    findAll()
 * @method OrderSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderSubscription::class);
    }

    public function getOrderSubscriptions($reference, $keyword, $recipeDate, $checkedin): QueryBuilder {
        $qb = $this->createQueryBuilder("t");
        $qb->select("t");

        if ($reference !== "all") {
            $qb->andWhere("t.reference = :reference")->setParameter("reference", $reference);
        }

        if ($keyword !== "all" || $recipeDate !== "all") {
            $qb->leftJoin("t.orderelement", "orderelement");
        }

        if ($keyword !== "all") {
            $qb->leftJoin("orderelement.order", "o");
            $qb->leftJoin("o.payment", "payment");
            $qb->andWhere("t.reference LIKE :keyword OR :keyword LIKE t.reference OR o.reference LIKE :keyword OR :keyword LIKE o.reference OR payment.clientEmail LIKE :keyword OR :keyword LIKE payment.clientEmail OR payment.firstname LIKE :keyword OR :keyword LIKE payment.firstname OR payment.lastname LIKE :keyword OR :keyword LIKE payment.lastname")->setParameter("keyword", "%" . trim($keyword) . "%");
        }

        if ($recipeDate !== "all") {
            $qb->leftJoin("orderelement.recipesubscription", "recipesubscription");
            $qb->leftJoin("recipesubscription.recipedate", "recipedate");
            $qb->andWhere("recipedate.reference = :recipedate")->setParameter("recipedate", $recipeDate);
        }

        if ($checkedin !== "all") {
            if ($checkedin == "1") {
                $qb->andWhere("t.scanned = 1");
            } elseif ($checkedin == "0") {
                $qb->andWhere("t.scanned = 0");
            }
        }

        $qb->orderBy("t.createdAt", "DESC");

        return $qb;
    }
}
