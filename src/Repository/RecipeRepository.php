<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\Traits\HasLimit;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

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
            // ->select('r', 'c')
            ->where('r.duration <= :duration')
            ->setParameter('duration', $duration)
            ->orderBy('r.duration', 'ASC')
            // ->leftJoin('r.category', 'c')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTotalDuration(): int
    {
        return (int) $this->createQueryBuilder('r')
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
                'sortFieldAllowList' => ['r.id', 'r.title', 'r.category'],
            ]
        );
    }

    /**
     * @return QueryBuilder<Recipe>
     */
    public function findLastRecent(int $maxResults): QueryBuilder // (HomeController)
    {
        return $this->createQueryBuilder('r')
            // ->select('r')
            // ->where('r.isOnline = true AND r.createdAt < NOW()')
            ->where('r.isOnline = true')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($maxResults)
        ;
    }

    public function findLatest(int $maxResults): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.isOnline = :isOnline')
            ->setMaxResults($maxResults)
            ->setParameter('isOnline', true)
            ->getQuery()
            ->getResult()
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
                ->andWhere('r.createdAt < :isonline_at')
                ->setParameter('isonline_at', $date, Types::DATETIME_IMMUTABLE)
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
            ->leftJoin('r.restaurant', 'rest')
            ->where('rest.user = :user')
            ->andWhere('r.isOnline = true')
            ->orderBy('r.updatedAt', 'DESC')
            ->setMaxResults($maxResults)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getRecipes($category, $venue, $country, $location, $restaurant, $keyword, $slug, $freeonly, $onlineonly, $pricemin, $pricemax, $audience, $startdate, $startdatemin, $startdatemax, $isOnline, $elapsed, $restaurantEnabled, $addedtofavoritesby, $onsalebypos, $canbescannedby, $isOnHomepageSlider, $otherthan, $notId, $sort, $order, $limit, $count): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');

        if ($count) {
            $qb->select('COUNT(r)');
        } else {
            $qb->select('r');
        }

        /*
        if ('all' !== $keyword || 'all' !== $slug || $otherthan) {
            $qb->join('r.translations', 'translations');
        }
        */

        if ('all' !== $category) {
            $qb->leftJoin('r.category', 'category');
            //$qb->join('category.translations', 'categorytranslations');
            $qb->andWhere('category.slug = :category')->setParameter('category', $category);
        }

        if ('all' !== $venue || 'all' !== $country || 'all' !== $location || 'all' !== $pricemin || 'all' !== $pricemax || 'all' != $startdate || 'all' != $startdatemin || 'all' != $startdatemax || 'recipedates.startdate' === $sort || 'all' !== $elapsed || 'all' !== $onsalebypos || 'all' !== $onlineonly) {
            $qb->leftJoin('r.recipedates', 'recipedates');
            $qb->leftJoin('recipedates.venue', 'venue');
        }

        if ('1' == $onlineonly) {
            $qb->andWhere('recipedates.online = 1');
        }

        if ('1' == $freeonly || 'all' !== $pricemin || 'all' !== $pricemax) {
            $qb->leftJoin('recipedates.subscriptions', 'subscriptions');
        }

        if ('1' == $freeonly) {
            $qb->andWhere('subscriptions.free = 1');
        } elseif ('all' !== $pricemin || 'all' !== $pricemax) {
            if ('all' !== $pricemin) {
                $qb->andWhere('(subscriptions.price >= :pricemin AND subscriptions.promotionalprice IS NULL) OR (subscriptions.promotionalprice >= :pricemin)')->setParameter('pricemin', $pricemin);
            }
            if ('all' !== $pricemax) {
                $qb->andWhere('((subscriptions.price <= :pricemax OR subscriptions.price IS NULL) AND subscriptions.promotionalprice IS NULL) OR (subscriptions.promotionalprice <= :pricemax)')->setParameter('pricemax', $pricemax);
            }
        }

        if ('all' !== $startdate) {
            $qb->andWhere('Date(recipedates.startdate) = :startdate')->setParameter('startdate', $startdate);
        }

        if ('all' !== $startdatemin) {
            $qb->andWhere('Date(recipedates.startdate) >= :startdatemin')->setParameter('startdatemin', $startdatemin);
        }

        if ('all' !== $startdatemax) {
            $qb->andWhere('Date(recipedates.startdate) <= :startdatemax')->setParameter('startdatemax', $startdatemax);
        }

        if ('all' !== $audience) {
            $qb->leftJoin('r.audiences', 'audiences');
            //$qb->leftJoin('audiences.translations', 'audiencestranslations');
            $qb->andWhere('audiences.slug = :audience')->setParameter('audience', $audience);
        }

        if ('all' !== $venue) {
            $qb->leftJoin('venue.translations', 'venuetranslations');
            $qb->andWhere('venuetranslations.slug = :venue')->setParameter('venue', $venue);
        }

        if ('all' !== $country || 'all' !== $location) {
            $qb->leftJoin('venue.country', 'country');
            $qb->leftJoin('country.translations', 'countrytranslations');
        }

        if ('all' !== $country) {
            $qb->andWhere('countrytranslations.slug = :country')->setParameter('country', $country);
        }

        if ('all' !== $location) {
            $qb->andWhere('countrytranslations.name LIKE :location or :location LIKE countrytranslations.name or venue.state LIKE :location or :location LIKE venue.state or venue.city LIKE :location or :location LIKE venue.city')->setParameter('location', $location);
        }

        if ('all' !== $restaurant || 'all' !== $restaurantEnabled) {
            $qb->leftJoin('r.restaurant', 'restaurant');
        }

        if ('all' !== $restaurant) {
            $qb->andWhere('restaurant.slug = :restaurant')->setParameter('restaurant', $restaurant);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('r.title LIKE :keyword or :keyword LIKE r.title or r.content LIKE :keyword or :keyword LIKE r.content or r.tags LIKE :keyword or :keyword LIKE r.tags or r.authors LIKE :keyword or :keyword LIKE r.authors')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere('r.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $addedtofavoritesby) {
            $qb->andWhere(':addedtofavoritesbyuser MEMBER OF r.addedtofavoritesby')->setParameter('addedtofavoritesbyuser', $addedtofavoritesby);
        }

        if ('all' !== $onsalebypos) {
            $qb->andWhere(':onsalebypos MEMBER OF recipedates.pointofsales')->setParameter('onsalebypos', $onsalebypos);
        }

        if ('all' !== $canbescannedby) {
            $qb->andWhere(':canbescannedby MEMBER OF recipedates.scanners')->setParameter('canbescannedby', $canbescannedby);
        }

        if (true === $isOnHomepageSlider) {
            $qb->andWhere('r.isonhomepageslider IS NOT NULL');
        }

        if ('all' !== $isOnline) {
            $qb->andWhere('r.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $otherthan) {
            $qb->andWhere('r.slug != :otherthan')->setParameter('otherthan', $otherthan);
        }

        if ('all' !== $notId) {
            $qb->andWhere('r.id != :notId')->setParameter('notId', $notId);
        }

        if ('all' !== $elapsed) {
            if (true === $elapsed || '1' == $elapsed) {
                $qb->andWhere('recipedates.startdate < CURRENT_TIMESTAMP()');
            } elseif (false === $elapsed || '0' == $elapsed) {
                $qb->andWhere('recipedates.startdate >= CURRENT_TIMESTAMP()');
            }
        }

        if ('all' !== $restaurantEnabled) {
            $qb->leftJoin('restaurant.user', 'user');
            $qb->andWhere('user.isVerified = :userEnabled')->setParameter('userEnabled', $restaurantEnabled);
        }

        $qb->orderBy($sort, $order);
        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }
}
