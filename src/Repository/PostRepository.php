<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Keyword;
use App\DTO\SearchDataDTO;
use App\Entity\PostCategory;
use App\Entity\Traits\HasLimit;
use function Symfony\Component\String\u;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry, 
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, Post::class);
    }

    public function findMostCommented(int $maxResults): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.comments', 'c')
            ->addSelect('COUNT(c) AS HIDDEN numberOfComments')
            ->andWhere('c.isApproved = true')
            ->groupBy('p')
            ->orderBy('numberOfComments', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Post[] Returns an array of Post objects
     */
    public function findLastRecent(int $maxResults): array // (HomeController)
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setParameter('now', new \DateTimeImmutable())
            ->where('p.createdAt <= :now')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get published posts thanks to Search Data value.
     */
    public function findBySearch(SearchDataDTO $searchData): PaginationInterface
    {
        $data = $this->createQueryBuilder('p')
            ->where('p.createdAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->addOrderBy('p.createdAt', 'DESC')
        ;

        if (!empty($searchData->keywords)) {
            $data = $data
                ->join('p.keywords', 'k')
                ->andWhere('p.title LIKE :keywords')
                ->orWhere('k.name LIKE :keywords')
                ->setParameter('keywords', "%{$searchData->keywords}%")
            ;
        }

        if (!empty($searchData->categories)) {
            $data = $data
                ->join('p.postcategories', 'c')
                ->andWhere('c.id IN (:postcategories)')
                ->setParameter('postcategories', $searchData->categories)
            ;
        }

        $data = $data
            ->getQuery()
            ->getResult()
        ;

        $pagination = $this->paginatorInterface->paginate($data, $searchData->page, 9);

        return $pagination;
    }

    /**
     * Get published posts.
     *
     * @param ?PostCategory $postCategory
     * @param ?Keyword $keyword
     */
    public function findPublished(
        int $page,
        PostCategory $postCategory = null,
        Keyword $keyword = null,
    ): PaginationInterface {
        $data = $this->createQueryBuilder('p')
            ->where('p.createdAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->addOrderBy('p.createdAt', 'DESC')
        ;

        if (isset($postCategory)) {
            $data = $data
                ->join('p.postcategories', 'c')
                ->andWhere(':postCategory IN (c)')
                ->setParameter('postCategory', $postCategory)
            ;
        }

        if (isset($keyword)) {
            $data = $data
                ->join('p.keywords', 'k')
                ->andWhere(':keyword IN (k)')
                ->setParameter('keyword', $keyword)
            ;
        }

        $data
            ->getQuery()
            ->getResult()
        ;

        $pagination = $this->paginator->paginate($data, $page, 6);

        return $pagination;
    }

    public function findForPagination(int $page, ?int $userId): PaginationInterface
    {
        $builder = $this->createQueryBuilder('p')->leftJoin('p.postcategories', 'c')->leftJoin('p.keywords', 'k')->select('p', 'c', 'k');

        if ($userId) {
            $builder = $builder->andWhere('p.author = :user')->setParameter('user', $userId);
        }

        return $this->paginator->paginate(
            $builder,
            $page,
            HasLimit::POST_LIMIT,
            [
                'distinct' => false,
                'sortFieldAllowList' => ['p.id', 'p.title', 'p.postcategories', 'p.keywords']
            ]
        );
    }

    /**
     * @return Post[]
     */
    public function findBySearchQuery(string $query, int $limit = 10): array
    {
        $searchTerms = $this->extractSearchTerms($query);

        if (0 === \count($searchTerms)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('p');

        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.title LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

        /** @var Post[] $result */
        $result = $queryBuilder
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    /**
     * Transforms the search string into an array of search terms.
     *
     * @return string[]
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $terms = array_unique(u($searchQuery)->replaceMatches('/[[:space:]]+/', ' ')->trim()->split(' '));

        // ignore the search terms that are too short
        return array_filter($terms, static function ($term) {
            return 2 <= $term->length();
        });
    }
}
