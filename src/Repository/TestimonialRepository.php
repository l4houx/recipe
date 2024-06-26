<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Query;
use App\Entity\Testimonial;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Traits\HasLimit;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Testimonial>
 *
 * @method Testimonial|null find($id, $lockMode = null, $lockVersion = null)
 * @method Testimonial|null findOneBy(array $criteria, array $orderBy = null)
 * @method Testimonial[]    findAll()
 * @method Testimonial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TestimonialRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, Testimonial::class);
    }

    public function findForPagination(int $page): PaginationInterface // TestimonialController
    {
        $builder = $this->createQueryBuilder('t')
            ->orderBy('t.updatedAt', 'DESC')
            ->setParameter('now', new \DateTimeImmutable())
            ->where('t.updatedAt <= :now')
            ->orWhere('t.isOnline = true')
        ;

        return $this->paginator->paginate(
            $builder->getQuery()->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                TranslationWalker::class
            ),
            $page,
            HasLimit::TESTIMONIAL_LIMIT,
            ['wrap-queries' => true],
            [
                'distinct' => false,
                'sortFieldAllowList' => ['t.id'],
            ]
        );
    }

    /**
     * @return Testimonial[] Returns an array of Testimonial objects
     */
    public function findLastRecent(int $maxResults): array // (PageController, HomeController)
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC')
            ->where('t.isOnline = true')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Retrieves the latest testimonials created by the user.
     *
     * @return Testimonial[] Returns an array of Testimonial objects
     */
    public function findLastByUser(User $user, int $maxResults): array // AccountTestimonialController
    {
        return $this->createQueryBuilder('t')
            ->where('t.author = :user')
            ->orderBy('t.updatedAt', 'DESC')
            ->setMaxResults($maxResults)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the testimonials after applying the specified search criterias.
     *
     * @param string      $keyword
     * @param int         $id
     * @param string      $slug
     * @param User|null   $user
     * @param bool        $isOnline
     * @param int|null    $rating
     * @param int         $minrating
     * @param int         $maxrating
     * @param int         $limit
     * @param int         $count
     * @param string      $sort
     * @param string      $order
     */
    public function getTestimonials($keyword, $id, $slug, $user, $isOnline, $rating, $minrating, $maxrating, $limit, $count, $sort, $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');

        if ($count) {
            $qb->select('COUNT(t)');
        } else {
            $qb->select('t');
        }

        if ('all' !== $keyword) {
            $qb->andWhere('t.headline LIKE :keyword or :keyword LIKE t.headline or t.content LIKE :keyword or :keyword LIKE t.content')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $id) {
            $qb->andWhere('t.id = :id')->setParameter('id', $id);
        }

        if ('all' !== $slug) {
            $qb->andWhere('t.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $user) {
            $qb->leftJoin('t.author', 'user');
            $qb->andWhere('user.slug = :user')->setParameter('user', $user);
        }

        if ('all' !== $isOnline) {
            $qb->andWhere('t.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $rating) {
            $qb->andWhere('t.rating = :rating')->setParameter('rating', $rating);
        }

        if ('all' !== $minrating) {
            $qb->andWhere('t.rating >= :minrating')->setParameter('minrating', $minrating);
        }

        if ('all' !== $maxrating) {
            $qb->andWhere('t.rating <= :maxrating')->setParameter('maxrating', $maxrating);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ($sort) {
            $qb->orderBy('t.'.$sort, $order);
        }

        return $qb;
    }
}
