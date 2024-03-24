<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Transaction;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Transaction>
 *
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * @return Transaction[]
     */
    public function findFor(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.author = :user')
            ->orderBy('t.createdAt', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function getMonthlyRevenues(): array
    {
        return $this->aggregateRevenus('yyyy-mm', 'mm', 24);
    }

    public function getDailyRevenues(): array
    {
        return $this->aggregateRevenus('yyyy-mm-dd', 'dd', 30);
    }

    private function aggregateRevenus(string $group, string $label, int $limit): array
    {
        return array_reverse($this->createQueryBuilder('t')
            ->select(
                "date_format(t.createdAt, '$label') as date",
                "date_format(t.createdAt, '$group') as fulldate",
                'ROUND(SUM(t.price - t.tax - t.fee)) as amount'
            )
            ->groupBy('fulldate', 'date')
            ->where('t.refunded = false')
            ->orderBy('fulldate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult())
        ;
    }

    public function getMonthlyReport(int $year): array
    {
        return $this->createQueryBuilder('t')
            ->select(
                't.method as method',
                'EXTRACT(MONTH FROM t.createdAt) as month',
                'ROUND(SUM(t.price) * 100) / 100 as price',
                'ROUND(SUM(t.tax) * 100) / 100 as tax',
                'ROUND(SUM(t.fee) * 100) / 100 as fee',
            )
            ->groupBy('month', 't.method')
            ->where('t.refunded = false')
            ->andWhere('EXTRACT(YEAR FROM t.createdAt) = :year')
            ->setParameter('year', $year)
            ->orderBy('month', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
