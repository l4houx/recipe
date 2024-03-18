<?php

namespace App\Repository;

use App\Entity\Faq;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Faq>
 *
 * @method Faq|null find($id, $lockMode = null, $lockVersion = null)
 * @method Faq|null findOneBy(array $criteria, array $orderBy = null)
 * @method Faq[]    findAll()
 * @method Faq[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    public function getFaqs(string $question, string $answer): QueryBuilder
    {
        $qb = $this->createQueryBuilder('f');
        $qb->select('f');
        $qb->join('f.translations', 'translations');

        if ('all' !== $question) {
            $qb->andWhere('translations.question = :question')->setParameter('question', $question);
        }

        if ('all' !== $answer) {
            $qb->andWhere('translations.answer = :answer')->setParameter('answer', $answer);
        }

        return $qb;
    }

    /**
     * Retrieves questions/answers randomly.
     * @return Faq[]
     */
    public function findRand(int $maxResults): array // HelpCenterController
    {
        return $this->createQueryBuilder('f')
            ->orderBy('Rand()')
            ->where('f.isOnline = true')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Retrieves all questions/answers.
     * @return Faq[]
     */
    public function findAlls() // HelpCenterController
    {
        return $this->createQueryBuilder('f')
            ->where('f.isOnline = true')
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
