<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use App\Entity\HelpCenterArticle;
use App\Entity\HelpCenterCategory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<HelpCenterArticle>
 *
 * @method HelpCenterArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpCenterArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpCenterArticle[]    findAll()
 * @method HelpCenterArticle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpCenterArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpCenterArticle::class);
    }

    /**
     * Returns the help center articles after applying the specified search criterias.
     *
     * @param string             $selecttags
     * @param bool               $isOnline
     * @param bool               $isFeatured
     * @param string             $keyword
     * @param string             $slug
     * @param HelpCenterCategory $category
     * @param int                $limit
     * @param string             $sort
     * @param string             $order
     * @param string             $otherthan
     *
     * @return QueryBuilder<HelpCenterArticle> (HelpCenterArticleController)
     */
    public function getHelpCenterArticles($selecttags, $isOnline, $isFeatured, $keyword, $slug, $category, $limit, $sort, $order, $otherthan): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');

        if (!$selecttags) {
            $qb->select('a');

            if ('all' !== $isOnline) {
                $qb->andWhere('a.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
            }

            if ('all' !== $isFeatured) {
                $qb->andWhere('a.isFeatured = :isFeatured')->setParameter('isFeatured', $isFeatured);
            }

            if ('all' !== $keyword) {
                $qb->andWhere('a.title LIKE :keyword or :keyword LIKE a.title or :keyword LIKE a.content or a.content LIKE :keyword')->setParameter('keyword', '%'.$keyword.'%');
            }

            if ('all' !== $slug) {
                $qb->andWhere('a.slug = :slug')->setParameter('slug', $slug);
            }

            if ('all' !== $category) {
                $qb->leftJoin('a.category', 'category');
                $qb->andWhere('category.slug = :category')->setParameter('category', $category);
            }

            if ('all' !== $limit) {
                $qb->setMaxResults($limit);
            }

            if ('all' !== $otherthan) {
                $qb->andWhere('a.slug != :otherthan')->setParameter('otherthan', $otherthan);
                $qb->andWhere('a.slug = :otherthan')->setParameter('otherthan', $otherthan);
            }

            $qb->orderBy('a.'.$sort, $order);
        } else {
            $qb->select("SUBSTRING_INDEX(GROUP_CONCAT(a.tags SEPARATOR ','), ',', 8)");
        }

        return $qb;
    }
}
