<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\RecipeDate;
use App\Entity\RecipeSubscription;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Returns the recipe subscriptions after applying the specified search criterias.
     *
     * @param string          $reference
     * @param Restaurant|null $restaurant
     * @param Recipe|null     $recipe
     * @param RecipeDate|null $recipedate
     * @param int             $limit
     *
     * @return QueryBuilder<RecipeSubscription>
     */
    public function getRecipeSubscriptions($reference, $restaurant, $recipe, $recipedate, $limit): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');
        $qb->select('r');

        if ('all' !== $reference) {
            $qb->andWhere('r.reference = :reference')->setParameter('reference', $reference);
        }

        if ('all' !== $recipe || 'all' !== $restaurant || 'all' !== $recipedate) {
            $qb->leftJoin('r.recipedate', 'recipedate');
        }

        if ('all' !== $recipe || 'all' !== $restaurant) {
            $qb->leftJoin('recipedate.recipe', 'recipe');
        }

        if ('all' !== $restaurant) {
            $qb->leftJoin('recipe.restaurant', 'restaurant');
            $qb->andWhere('restaurant.slug = :restaurant')->setParameter('restaurant', $restaurant);
        }

        if ('all' !== $recipe) {
            $qb->leftJoin('recipe.translations', 'recipetranslations');
            $qb->andWhere('recipetranslations.slug = :recipe')->setParameter('recipe', $recipe);
        }

        if ('all' !== $recipedate) {
            $qb->andWhere('recipedate.reference = :recipedate')->setParameter('recipedate', $recipedate);
        }

        $qb->orderBy('r.id', 'ASC');

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
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
