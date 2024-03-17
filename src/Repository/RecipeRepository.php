<?php

namespace App\Repository;

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
}
