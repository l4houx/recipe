<?php

namespace App\Repository\Setting;

use App\Entity\Setting\Language;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Language>
 *
 * @method Language|null find($id, $lockMode = null, $lockVersion = null)
 * @method Language|null findOneBy(array $criteria, array $orderBy = null)
 * @method Language[]    findAll()
 * @method Language[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LanguageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Language::class);
    }

    /**
     * Returns the languages after applying the specified search criterias.
     *
     * @param bool   $isOnline
     * @param string $keyword
     * @param string $slug
     * @param int    $limit
     * @param string $sort
     * @param string $order
     */
    public function getLanguages($isOnline, $keyword, $slug, $limit, $sort, $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l');
        $qb->select('l');

        if ('all' !== $isOnline) {
            $qb->andWhere('l.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('l.name LIKE :keyword or :keyword LIKE l.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere('l.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy($sort, $order);

        return $qb;
    }
}
