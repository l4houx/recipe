<?php

namespace App\Repository\Setting;

use App\Entity\Setting\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Currency>
 *
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Currency[]    findAll()
 * @method Currency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    /**
     * Returns the currencies after applying the specified search criterias.
     *
     * @param string      $ccy
     * @param string|null $symbol
     *
     * @return QueryBuilder (AccountController)
     */
    public function getCurrencies($ccy, $symbol): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c');

        if ('all' !== $ccy) {
            $qb->andWhere('c.ccy = :ccy')->setParameter('ccy', $ccy);
        }

        if ('all' !== $symbol) {
            $qb->andWhere('c.symbol = :symbol')->setParameter('symbol', $symbol);
        }

        return $qb;
    }
}
