<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserEmailVerification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserEmailVerification>
 *
 * @method UserEmailVerification|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserEmailVerification|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserEmailVerification[]    findAll()
 * @method UserEmailVerification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserEmailVerificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserEmailVerification::class);
    }

    public function findLastForUser(User $user): ?UserEmailVerification
    {
        return $this->createQueryBuilder('v')
            ->where('v.author = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Delete old email verification requests.
     */
    public function clean(): int
    {
        return $this->createQueryBuilder('v')
            ->where('v.createdAt < :date')
            ->setParameter('date', new \DateTime('-1 month'))
            ->delete(UserEmailVerification::class, 'v')
            ->getQuery()
            ->execute()
        ;
    }
}
