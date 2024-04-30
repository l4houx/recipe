<?php

namespace App\Repository;

use App\Entity\PostType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostType>
 *
 * @method PostType|null find($id, $lockMode = null, $lockVersion = null)
 * @method PostType|null findOneBy(array $criteria, array $orderBy = null)
 * @method PostType[]    findAll()
 * @method PostType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostType::class);
    }

    public function getPostsTypes($isOnline, $keyword, $slug, $limit, $sort, $order, $hasposts): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p');
        $qb->addSelect('COUNT(p) as HIDDEN postscount');

        if ('all' !== $isOnline) {
            $qb->andWhere('p.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('p.name LIKE :keyword or :keyword LIKE p.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere('p.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (true === $hasposts || 1 === $hasposts) {
            $qb->join('p.posts', 'posts');
        }

        $qb->groupBy('p');

        return $qb;
    }
}
