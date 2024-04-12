<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Comment;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Traits\HasLimit;
use App\Service\SettingService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
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

    public function findForPagination(int $page): PaginationInterface // (PostCommentController)
    {
        $builder = $this->createQueryBuilder('c')
            ->andWhere('c.isApproved = true')
            ->orderBy('c.publishedAt', 'DESC')
            ->join('c.target', 't')
            ->leftJoin('c.author', 'a')
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
            ->andWhere('c.author = :user')
            ->andWhere('c.isApproved = true')
            ->orderBy('c.publishedAt', 'DESC')
            ->setMaxResults($maxResults)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryLatest(int $maxResults): Query
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.publishedAt', 'DESC')
            ->join('c.target', 't')
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
}
