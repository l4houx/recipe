<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Recipe;
use Doctrine\ORM\Query;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Traits\HasLimit;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Recipe>
 *
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry, 
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, Recipe::class);
    }

    public function deleteForUser(User $user): void
    {
        $this->createQueryBuilder('r')
            ->where('r.author = :user')
            ->setParameter('user', $user)
            ->delete()
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * @return Recipe[] Returns an array of Recipe objects
     */
    public function findWithDurationLowerThan(int $duration): array
    {
        return $this->createQueryBuilder('r')
            //->select('r', 'c')
            ->where('r.duration <= :duration')
            ->setParameter('duration', $duration)
            ->orderBy('r.duration', 'ASC')
            //->leftJoin('r.category', 'c')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTotalDuration(): int
    {
        return (int)$this->createQueryBuilder('r')
            ->select('SUM(r.duration) as total')
            ->where('r.isOnline = true')
            ->getQuery()
            ->getSingleScalarResult() ?: 0
        ;
    }

    /**
     * @return Recipe[] Returns an array of Recipe objects
     */
    public function findRandom(int $maxResults): array // RecipeController
    {
        return $this->createQueryBuilder('c')
            ->orderBy('RANDOM()')
            ->where('r.isOnline = true')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findForPagination(int $page, ?int $userId): PaginationInterface // RecipeController
    {
        $builder = $this->createQueryBuilder('r')->leftJoin('r.category', 'c')->select('r', 'c');

        if ($userId) {
            $builder = $builder->andWhere('r.author = :user')->setParameter('user', $userId);
        }

        return $this->paginator->paginate(
            $builder->getQuery()->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                TranslationWalker::class
            ),
            $page,
            HasLimit::RECIPE_LIMIT,
            [
                'distinct' => false,
                'sortFieldAllowList' => ['r.id', 'r.title', 'r.category']
            ]
        );
    }

    /**
     * @return QueryBuilder<Recipe>
     */
    public function findLastRecent(int $maxResults): QueryBuilder // (HomeController)
    {
        return $this->createQueryBuilder('r')
            //->select('r')
            //->where('r.isOnline = true AND r.createdAt < NOW()')
            ->where('r.isOnline = true')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($maxResults)
        ;
    }

    public function queryAll(bool $userPremium = true): QueryBuilder // RecipeController
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.isOnline = true')
            ->orderBy('r.createdAt', 'DESC')
        ;

        if (!$userPremium) {
            $date = new \DateTimeImmutable('+ 3 days');
            $qb = $qb
                ->andWhere('r.createdAt < :published_at')
                ->setParameter('published_at', $date, Types::DATETIME_IMMUTABLE)
            ;
        }

        return $qb;
    }

    public function queryAllPremium(): QueryBuilder // RecipeController
    {
        return $this->queryAll()
            ->andWhere('r.premium = :premium OR r.createdAt > NOW()')
            ->setParameter('premium', true)
        ;
    }

    /**
     * Retrieves the latest recipes created by the user.
     *
     * @return Recipe[]
     */
    public function findLastByUser(User $user, int $maxResults): array // MainController
    {
        return $this->createQueryBuilder('r')
            ->where('r.author = :user')
            ->andWhere('r.isOnline = true')
            ->orderBy('r.updatedAt', 'DESC')
            ->setMaxResults($maxResults)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the recipes after applying the specified search criterias
     *
     * @param string                   $keyword
     * @param string                   $slug
     * @param Collection               $addedtofavoritesby
     * @param null|HomepageHeroSetting $isOnHomepageSlider
     * @param bool                     $isOnline
     * @param mixed                    $otherthan
     * @param User|null                $user
     * @param bool                     $userEnabled
     * @param string                   $sort
     * @param string                   $order
     * @param int                      $limit
     * @param int                      $count
     *
     * @return QueryBuilder
     */
    public function getRecipes($keyword, $slug, $addedtofavoritesby, $isOnHomepageSlider, $isOnline, $otherthan, $user, $userEnabled,  $sort, $order, $limit, $count): QueryBuilder
    {
        $qb = $this->createQueryBuilder("r");

        if ($count) {
            $qb->select("COUNT(r)");
        } else {
            $qb->select("DISTINCT r");
        }

        if ('all' !== $keyword) {
            $qb->andWhere('r.title LIKE :keyword or :keyword LIKE r.title or :keyword LIKE r.content or r.content LIKE :keyword')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere("r.slug = :slug")->setParameter("slug", $slug);
        }

        if ('all' !== $addedtofavoritesby) {
            $qb->andWhere(":addedtofavoritesbyuser MEMBER OF r.addedtofavoritesby")->setParameter("addedtofavoritesbyuser", $addedtofavoritesby);
        }

        if (true === $isOnHomepageSlider) {
            $qb->andWhere("r.isonhomepageslider IS NOT NULL");
        }

        if ('all' !== $isOnline) {
            $qb->andWhere('r.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $otherthan) {
            $qb->andWhere("r.slug != :otherthan")->setParameter("otherthan", $otherthan);
            $qb->andWhere("r.slug = :otherthan")->setParameter("otherthan", $otherthan);
        }

        if ('all' !== $user || 'all' !== $userEnabled) {
            $qb->leftJoin('r.author', 'user');
        }

        if ('all' !== $user) {
            $qb->andWhere('user.slug = :user')->setParameter('user', $user);
        }

        if ('all' !== $userEnabled) {
            $qb->leftJoin('user', 'user');
            $qb->andWhere('user.enabled = :userEnabled')->setParameter('userEnabled', $userEnabled);
        }

        $qb->orderBy($sort, $order);

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }
}
