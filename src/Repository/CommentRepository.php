<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Traits\HasLimit;
use App\Entity\User;
use App\Entity\Venue;
use App\Service\SettingService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

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
    public function __construct(
        ManagerRegistry $registry,
        private readonly SettingService $settingService,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, Comment::class);
    }

    public function getIsActiveComments(): array // (PostCommentController)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isApproved = true')
            ->orderBy('c.publishedAt', 'ASC')
            ->join('c.target', 't')
            ->leftJoin('c.author', 'a')
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

        if ($value instanceof Venue) {
            $object = 'venue';
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

    public function findRecentComments($value)
    {
        if ($value instanceof Post) {
            $object = 'post';
        }

        if ($value instanceof Venue) {
            $object = 'venue';
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

    public function findForPagination($value, int $page): PaginationInterface // (PostCommentController)
    {
        if ($value instanceof Post) {
            $object = 'post';
        }

        if ($value instanceof Venue) {
            $object = 'venue';
        }

        $builder = $this->createQueryBuilder('c')
            ->andWhere('c.'.$object.' = :val')
            ->andWhere('c.isApproved = true')
            ->setParameter('val', $value->getId())
            ->orderBy('c.id', 'DESC')

            // ->andWhere('c.isApproved = true')
            // ->orderBy('c.publishedAt', 'DESC')
            // ->join('c.target', 't')
            // ->leftJoin('c.author', 'a')
        ;

        return $this->paginator->paginate(
            $builder->getQuery()->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                TranslationWalker::class
            ),
            $page,
            $this->settingService->getSettings('comments_per_page'),
            [
                'distinct' => false,
                'sortFieldAllowList' => ['c.id'],
            ]
        );
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
            ->join('c.venue', 'v')
            ->where('v.isOnline = true')
            ->andWhere('c.author = :user')
            ->andWhere('c.isApproved = true')
            ->orderBy('c.publishedAt', 'DESC')
            ->setMaxResults($maxResults)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryLatest($value, int $maxResults): Query
    {
        if ($value instanceof Post) {
            $object = 'post';
        }

        if ($value instanceof Venue) {
            $object = 'venue';
        }

        return $this->createQueryBuilder('c')
            ->andWhere('c.'.$object.' = :val')
            ->setParameter('val', $value->getId())
            ->orderBy('c.publishedAt', 'DESC')
            //->join('c.target', 't')
            ->leftJoin('c.author', 'a')
            ->addSelect('t', 'a')
            ->setMaxResults($maxResults)
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

    /**
     * Returns the comments after applying the specified search criterias.
     *
     * @param string       $keyword
     * @param int          $id
     * @param User|null    $user
     * @param bool         $isApproved
     * @param bool         $isRGPD
     * @param string       $ip
     * @param Post|null    $post
     * @param Venue|null   $venue
     * @param Comment|null $parent
     * @param int          $limit
     * @param int          $count
     * @param string       $sort
     * @param string       $order
     */
    public function getComments($keyword, $id, $user, $isApproved, $isRGPD, $ip, $post, $venue, $parent, $limit, $count, $sort, $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if ($count) {
            $qb->select('COUNT(DISTINCT c)');
        } else {
            $qb->select('DISTINCT c');
        }

        if ('all' !== $keyword) {
            $qb->andWhere('c.ip LIKE :keyword or :keyword LIKE c.ip or c.content LIKE :keyword or :keyword LIKE c.content')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $id) {
            $qb->andWhere('c.id = :id')->setParameter('id', $id);
        }

        if ('all' !== $user) {
            $qb->leftJoin('c.author', 'a');
            //$qb->andWhere('a.username = :user')->setParameter('user', $user);
        }

        if ('all' !== $isApproved) {
            $qb->andWhere('c.isApproved = :isApproved')->setParameter('isApproved', $isApproved);
        }

        if ('all' !== $isRGPD) {
            $qb->andWhere('c.isRGPD = :isRGPD')->setParameter('isRGPD', $isRGPD);
        }

        if ('all' !== $ip) {
            $qb->andWhere('c.ip = :ip')->setParameter('ip', $ip);
        }

        if ('all' !== $post || 'all' !== $venue) {
            $qb->leftJoin('c.post', 'post');
        }

        if ('all' !== $post) {
            $qb->leftJoin('c.post', 'p');
            $qb->andWhere('p.isOnline = true')->setParameter('post', $post);
        }

        if ('all' !== $venue) {
            $qb->leftJoin('c.venue', 'v');
            $qb->andWhere('v.isOnline = true')->setParameter('venue', $venue);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ($sort) {
            $qb->orderBy('c.'.$sort, $order);
        }

        return $qb;
    }
}
