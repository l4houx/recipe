<?php

declare(strict_types=1);

namespace App\Repository\Setting;

use App\Entity\Setting\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 *
 * @method Menu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Menu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Menu[]    findAll()
 * @method Menu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function getMenus(string $slug): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('m');

        if ('all' !== $slug) {
            $qb->andWhere('m.slug = :slug')->setParameter('slug', $slug);
        }

        return $qb;
    }
}
