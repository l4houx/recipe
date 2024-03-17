<?php

declare(strict_types=1);

namespace App\Repository\Setting;

use App\Entity\Setting\MenuElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuElement>
 *
 * @method MenuElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuElement[]    findAll()
 * @method MenuElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuElement::class);
    }
}
