<?php

namespace App\Repository\Setting;

use App\Entity\Setting\Page;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function getPages(string $slug): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p');

        if ('all' !== $slug) {
            $qb->andWhere('p.slug = :slug')->setParameter('slug', $slug);
        }

        return $qb;
    }
}