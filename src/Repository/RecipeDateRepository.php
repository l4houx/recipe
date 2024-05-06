<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\RecipeDate;
use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Returns the recipe dates after applying the specified search criterias.
     *
     * @param string          $reference
     * @param Restaurant|null $restaurant
     * @param Recipe|null     $recipe
     * @param int             $limit
     * @param int             $count
     *
     * @return QueryBuilder<RecipeDate>
     */
    public function getRecipeDates($reference, $restaurant, $recipe, $limit, $count): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');

        if ($count) {
            $qb->select('COUNT(r)');
        } else {
            $qb->select('r');
        }

        if ('all' !== $reference) {
            $qb->andWhere('r.reference = :reference')->setParameter('reference', $reference);
        }

        if ('all' !== $recipe || 'all' !== $restaurant) {
            $qb->leftJoin('r.recipe', 'recipe');
        }

        if ('all' !== $restaurant) {
            $qb->leftJoin('recipe.restaurant', 'restaurant');
            $qb->andWhere('restaurant.slug = :restaurant')->setParameter('restaurant', $restaurant);
        }

        if ('all' !== $recipe) {
           //$qb->leftJoin('recipe.translations', 'recipetranslations');
            $qb->andWhere('recipes.slug = :recipe')->setParameter('recipe', $recipe);
        }

        $qb->orderBy('r.startdate', 'ASC');

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }
}
