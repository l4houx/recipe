<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Traits\HasLimit;
use App\DTO\CategoryWithCountDTO;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
                'sortFieldAllowList' => ['c.id', 'c.name']
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
            ->getResult()
        ;
    }

    //    /**
    //     * @return Category[] Returns an array of Category objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Category
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
