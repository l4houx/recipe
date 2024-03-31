<?php

namespace App\Repository;

use App\Entity\RecipeImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecipeImage>
 *
 * @method RecipeImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeImage[]    findAll()
 * @method RecipeImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeImage::class);
    }

    //    /**
    //     * @return RecipeImage[] Returns an array of RecipeImage objects
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

    //    public function findOneBySomeField($value): ?RecipeImage
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
