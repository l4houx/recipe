<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getDailySignups(): array
    {
        return $this->aggregateSignup('yyyy-mm-dd', 'dd', 30);
    }

    public function getMonthlySignups(): array
    {
        return $this->aggregateSignup('yyyy-mm', 'mm', 24);
    }

    private function aggregateSignup(string $group, string $label, int $limit): array
    {
        return array_reverse($this->createQueryBuilder('u')
            ->select(
                "TO_CHAR(u.createdAt, '$label') as date",
                "TO_CHAR(u.createdAt, '$group') as fulldate",
                'COUNT(u.id) as amount'
            )
            ->groupBy('fulldate', 'date')
            ->orderBy('fulldate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult())
        ;
    }
}
