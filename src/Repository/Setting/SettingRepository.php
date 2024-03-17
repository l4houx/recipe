<?php

namespace App\Repository\Setting;

use App\Entity\Setting\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Setting>
 *
 * @method Setting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Setting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Setting[]    findAll()
 * @method Setting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function findAllForTwig(): array
    {
        return $this->createQueryBuilder('s', 's.name')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getValue(string $name): mixed
    {
        try {
            return $this->createQueryBuilder('s')
                ->select('s.value')
                ->where('s.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        } catch (NoResultException|NonUniqueResultException) {
            return null;
        }
    }

    public function getIndexQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->where('s.type IS NOT NULL')
            ->orderBy('s.label')
        ;
    }
}
