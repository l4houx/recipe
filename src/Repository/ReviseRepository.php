<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Revise;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Revise>
 *
 * @method Revise|null find($id, $lockMode = null, $lockVersion = null)
 * @method Revise|null findOneBy(array $criteria, array $orderBy = null)
 * @method Revise[]    findAll()
 * @method Revise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Revise::class);
    }

    public function findFor(User $user, Post $post): ?Revise
    {
        return $this->createQueryBuilder('r')
            ->where('r.author = :author')
            ->andWhere('r.target = :target')
            ->andWhere('r.status != :status')
            ->setParameters([
                'author' => $user,
                'target' => $post,
                'status' => Revise::ACCEPTED,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Revise[] Returns an array of Revise objects
     */
    public function findLatest(int $maxResults): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->andWhere('r.createdAt <= :now')
            ->orderBy('r.createdAt', 'DESC')
            ->setParameters([
                'status' => Revise::ACCEPTED,
                'now' => new \DateTime(),
            ])
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Revise[]
     */
    public function findPendingFor(User $user): array
    {
        return $this->queryAllForUser($user)
            ->andWhere('r.status = :status')
            ->setParameter('status', Revise::PENDING)
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryAllForUser(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->addSelect('p')
            ->leftJoin('r.target', 'p')
            ->where('r.author = :user')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(10)
            ->setParameter('user', $user)
        ;
    }
}
