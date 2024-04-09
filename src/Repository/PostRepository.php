<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\PostCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

use function Symfony\Component\String\u;

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
     * @return QueryBuilder<Post>
     */
    public function findRecent(int $maxResults): QueryBuilder // (HomeController)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.isOnline = true AND p.createdAt < NOW()')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($maxResults)
        ;
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

    /**
     * Returns the blog posts after applying the specified search criterias.
     *
     * @param string            $selecttags
     * @param bool              $isOnline
     * @param string            $keyword
     * @param string            $slug
     * @param PostCategory|null $category
     * @param int               $limit
     * @param string            $sort
     * @param string            $order
     * @param string            $otherthan
     *
     * @return QueryBuilder<Post> (PostController)
     */
    public function getBlogPosts($selecttags, $isOnline, $keyword, $slug, $category, $limit, $sort, $order, $otherthan): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');

        if (!$selecttags) {
            $qb->select('p');
            if ('all' !== $isOnline) {
                $qb->andWhere('p.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
            }
            if ('all' !== $keyword) {
                $qb->andWhere('p.title LIKE :keyword or :keyword LIKE p.title or :keyword LIKE p.content or p.content LIKE :keyword or :keyword LIKE p.tags or p.tags LIKE :keyword')->setParameter('keyword', '%'.$keyword.'%');
            }
            if ('all' !== $slug) {
                $qb->andWhere('p.slug = :slug')->setParameter('slug', $slug);
            }
            if ('all' !== $category) {
                $qb->leftJoin('p.category', 'category');
                $qb->andWhere('category.slug = :category')->setParameter('category', $category);
            }
            if ('all' !== $limit) {
                $qb->setMaxResults($limit);
            }
            if ('all' !== $otherthan) {
                $qb->andWhere('p.id != :otherthan')->setParameter('otherthan', $otherthan);
            }
            $qb->orderBy('p.'.$sort, $order);
        } else {
            $qb->select("SUBSTRING_INDEX(GROUP_CONCAT(p.tags SEPARATOR ','), ',', 8)");
        }

        return $qb;
    }
}
