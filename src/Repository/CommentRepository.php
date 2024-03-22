<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Comment;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Traits\HasLimit;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findRecentComments($value) // (BlogController)
    {
        if ($value instanceof Post) {
            $object = 'post';
        }

        if ($value instanceof Recipe) {
            $object = 'recipe';
        }

        return $this->createQueryBuilder('c')
            ->andWhere('c.'.$object.' = :val')
            ->andWhere('c.isApproved = true')
            ->setParameter('val', $value->getId())
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<array-key, Comment>
     */
    public function getCommentsByEntityAndPage($value, int $page): array
    {
        if ($value instanceof Post) {
            $object = 'post';
        }

        if ($value instanceof Recipe) {
            $object = 'recipe';
        }

        return $this->createQueryBuilder('c')
            ->andWhere('c.'.$object.' = :val')
            ->andWhere('c.isApproved = true')
            ->setParameter('val', $value->getId())
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(HasLimit::COMMENT_LIMIT)
            ->setFirstResult(($page - 1) * HasLimit::COMMENT_LIMIT)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Retrieves the latest comments created by the user.
     *
     * @return Comment[] Returns an array of Comments objects
     */
    public function findLastByUser(User $user, int $maxResults): array //  (UserController)
    {
        return $this->createQueryBuilder('c')
            ->join('c.post', 'p')
            ->where('p.isOnline = true')
            ->andWhere('c.author = :user')
            ->andWhere('c.isApproved = true')
            ->orderBy('c.publishedAt', 'DESC')
            ->setMaxResults($maxResults)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public static function createIsActiveCriteria(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('isApproved', true))
            ->orderBy(['publishedAt' => 'ASC']);
    }

    public function queryLatest(): Query
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->join('c.target', 't')
            ->leftJoin('c.author', 'a')
            ->addSelect('t', 'a')
            ->setMaxResults(7)
            ->getQuery()
        ;
    }

    public function queryByIp(string $ip): QueryBuilder
    {
        return $this->createQueryBuilder('row')
            ->where('row.ip LIKE :ip')
            ->setParameter('ip', $ip)
        ;
    }
}
