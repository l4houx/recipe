<?php

namespace App\Repository;

use App\Entity\Content;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Content>
 *
 * @method Content|null find($id, $lockMode = null, $lockVersion = null)
 * @method Content|null findOneBy(array $criteria, array $orderBy = null)
 * @method Content[]    findAll()
 * @method Content[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    /**
     * @return QueryBuilder<Content>
     */
    public function findLatest(int $maxResults = 5, bool $withPremium = true): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->where('c.isOnline = true')
            ->setMaxResults($maxResults)
        ;

        if (!$withPremium) {
            $date = new \DateTimeImmutable('+ 3 days');
            $qb = $qb
                ->andWhere('c.createdAt < :published_at')
                ->setParameter('published_at', $date, Types::DATETIME_IMMUTABLE)
            ;
        }

        return $qb;
    }

    /**
     * @return QueryBuilder<Content>
     */
    public function findLatestPublished(int $maxResults = 5): QueryBuilder
    {
        return $this->findLatest($maxResults)->andWhere('c.createdAt < NOW()');
    }
}
