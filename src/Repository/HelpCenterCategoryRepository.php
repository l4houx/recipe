<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use App\Entity\HelpCenterCategory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<HelpCenterCategory>
 *
 * @method HelpCenterCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method HelpCenterCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method HelpCenterCategory[]    findAll()
 * @method HelpCenterCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HelpCenterCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpCenterCategory::class);
    }

    /**
     * Returns the help center articles after applying the specified search criterias.
     *
     * @param HelpCenterCategory|null $parent
     * @param bool                    $isOnline
     * @param string                  $keyword
     * @param string                  $slug
     * @param int                     $limit
     * @param string                  $order
     * @param string                  $sort
     *
     * @return QueryBuilder<HelpCenterCategory> (HelpCenterCategoryController)
     */
    public function getHelpCenterCategories($parent, $isOnline, $keyword, $slug, $limit, $order, $sort): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('c');

        if ('all' !== $parent) {
            if ('none' === $parent) {
                $qb->andWhere('c.parent IS NULL');
            } elseif ('notnull' === $parent) {
                $qb->andWhere('c.parent IS NOT NULL');
            } else {
                $qb->leftJoin('c.parent', 'parentcategory');
                // $qb->leftJoin("parentcategory.translations", "parentcategorytranslations");
                $qb->andWhere('c.slug = :parentcategory');
                $qb->setParameter('parentcategory', $parent);
            }
        }

        if ('all' !== $isOnline) {
            $qb->andWhere('c.isOnline = :isOnline')->setParameter('isOnline', $isOnline);
        }

        if ('all' !== $keyword) {
            $qb->andWhere('c.name LIKE :keyword or :keyword LIKE c.name')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $slug) {
            $qb->andWhere('c.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ('articlescount' === $order) {
            $qb->leftJoin('c.articles', 'articles');
            $qb->addSelect('COUNT(articles.id) AS HIDDEN articlescount');
            $qb->orderBy('articlescount', 'DESC');
            $qb->groupBy('c.id');
        } else {
            $qb->orderBy($order, $sort);
        }

        return $qb;
    }
}
