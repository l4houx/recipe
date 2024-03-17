<?php

declare(strict_types=1);

namespace App\Repository\Setting;

use App\Entity\Setting\AppLayoutSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppLayoutSetting>
 *
 * @method AppLayoutSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppLayoutSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppLayoutSetting[]    findAll()
 * @method AppLayoutSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class AppLayoutSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppLayoutSetting::class);
    }
}
