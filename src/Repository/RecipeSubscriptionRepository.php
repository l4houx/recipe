<?php

namespace App\Repository;

use App\Entity\RecipeSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecipeSubscription>
 *
 * @method RecipeSubscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeSubscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeSubscription[]    findAll()
 * @method RecipeSubscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeSubscription::class);
    }

    //    /**
    //     * @return RecipeSubscription[] Returns an array of RecipeSubscription objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RecipeSubscription
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
