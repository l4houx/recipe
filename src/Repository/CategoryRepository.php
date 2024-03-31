<?php

namespace App\Repository;

use App\DTO\CategoryWithCountDTO;
use App\Entity\Category;
use App\Entity\Traits\HasLimit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Gedmo\Translatable\TranslatableListener;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, Category::class);
    }

    public function findForPagination(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c')
            ->leftJoin('c.recipes', 'r')
            ->select('c', 'r'),
            $page,
            HasLimit::CATEGORY_LIMIT,
            [
                'distinct' => false,
                'sortFieldAllowList' => ['c.id', 'c.name'],
            ]
        );
    }

    /**
     * @return CategoryWithCountDTO[]
     */
    public function findAllWithCount(): array
    {
        return $this->createQueryBuilder('c')
            ->select('NEW App\\DTO\\CategoryWithCountDTO(c.id, c.name, COUNT(c.id), c.color, c.createdAt, c.updatedAt)')
            ->leftJoin('c.recipes', 'r')
            ->groupBy('c.id')
            ->getQuery()
            ->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                TranslationWalker::class
            )
            ->setHint(TranslatableListener::HINT_FALLBACK, 1)
            ->getResult()
        ;
    }

    /**
     * Returns the categories after applying the specified search criterias.
     *
     * @param bool   $isOnline
     * @param string $keyword
     * @param string $slug
     * @param bool   $isFeatured
     * @param int    $limit
     * @param string $sort
     * @param string $order
     */
    public function getCategories($isOnline, $keyword, $slug, $isFeatured, $limit, $sort, $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('DISTINCT c');

        if ($isOnline !== "all") {
            $qb->andWhere('c.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ($keyword !== "all") {
            $qb->andWhere('c.name LIKE :keyword or :keyword LIKE c.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ($slug !== "all") {
            $qb->andWhere('c.slug = :slug')->setParameter('slug', $slug);
        }

        if ($isFeatured !== "all") {
            $qb->andWhere('c.isFeatured = :isFeatured')->setParameter('isFeatured', $isFeatured);
            if (true === $isFeatured) {
                $qb->orderBy('c.featuredorder', 'ASC');
            }
        }

        if ($limit !== "all") {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy($sort, $order);

        return $qb;
    }
}
