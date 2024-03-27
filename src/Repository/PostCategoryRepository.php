<?php

namespace App\Repository;

use App\Entity\PostCategory;
use App\Entity\Traits\HasLimit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<PostCategory>
 *
 * @method PostCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostCategory[]    findAll()
 * @method PostCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostCategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, PostCategory::class);
    }

    public function findForPagination(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
            ->leftJoin('c.posts', 'p')
            ->select('c', 'p')
            ->orderBy('c.createdAt', 'DESC'),
            $page,
            HasLimit::POSTCATEGORY_LIMIT,
            [
                'distinct' => false,
                'sortFieldAllowList' => ['c.id', 'c.name'],
            ]
        );
    }

    /**
     * Returns the blog posts categories after applying the specified search criterias.
     *
     * @param PostCategory|null $parent
     * //@param bool   $isOnline
     * @param string $keyword
     * @param string $slug
     * @param int    $limit
     * @param string $order
     * @param string $sort
     *
     * @return QueryBuilder<PostCategory> (BlogCategoryController)
     */
    public function getBlogPostCategories($parent, /*$isOnline,*/ $keyword, $slug, $limit, $order, $sort): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c');

        if ('all' !== $parent) {
            if ('none' === $parent) {
                $qb->andWhere('c.parent IS NULL');
            } elseif ('notnull' === $parent) {
                $qb->andWhere('c.parent IS NOT NULL');
            } else {
                $qb->leftJoin('c.parent', 'parentcategory');
                // $qb->leftJoin("parentcategory.translations", "parentcategorytranslations");
                $qb->andWhere('c.slug = :parentcategory');
                $qb->setParameter('parentcategory', $parent);
            }
        }

        /*
        if ('all' !== $isOnline) {
            $qb->andWhere('c.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }
        */

        if ('all' !== $keyword) {
            $qb->andWhere('c.name LIKE :keyword or :keyword LIKE c.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere('c.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ('postscount' == $order) {
            $qb->leftJoin('c.posts', 'posts');
            $qb->addSelect('COUNT(posts.id) AS HIDDEN postscount');
            $qb->orderBy('postscount', 'DESC');
            $qb->groupBy('c.id');
        } else {
            $qb->orderBy($order, $sort);
        }

        return $qb;
    }
}
