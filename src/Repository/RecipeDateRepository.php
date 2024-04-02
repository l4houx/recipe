<?php

namespace App\Repository;

use App\Entity\RecipeDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecipeDate>
 *
 * @method RecipeDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeDate|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeDate[]    findAll()
 * @method RecipeDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeDate::class);
    }

    //    /**
    //     * @return RecipeDate[] Returns an array of RecipeDate objects
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

    //    public function findOneBySomeField($value): ?RecipeDate
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
