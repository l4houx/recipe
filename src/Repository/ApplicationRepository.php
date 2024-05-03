<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Application>
 *
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $application, string $newHashedToken): void
    {
        if (!$application instanceof Application) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $application::class));
        }

        $application->setToken($newHashedToken);
        $this->getEntityManager()->persist($application);
        $this->getEntityManager()->flush();
    }

    /**
     * @return Application[]
     */
    public function findAlls()
    {
        return $this->createQueryBuilder('a')
            //->where('a.isOnline = true')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the applications after applying the specified search criterias.
     *
     * @param string      $keyword
     * @param int         $id
     * @param User|null   $user
     * @param string|null $name
     * @param string|null $token
     * @param Collection  $tickets
     * @param array       $roles
     * @param int         $limit
     * @param int         $count
     * @param string      $sort
     * @param string      $order
     */
    public function getApplications($keyword, $id, $user, $name, $token, $tickets, $roles, $limit, $count, $sort, $order): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a');

        if ($count) {
            $qb->select('COUNT(DISTINCT a)');
        } else {
            $qb->select('DISTINCT a');
        }

        if ('all' !== $keyword) {
            $qb->andWhere('a.name LIKE :keyword or :keyword LIKE a.name or a.token LIKE :keyword or :keyword LIKE a.token')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $id) {
            $qb->andWhere('a.id = :id')->setParameter('id', $id);
        }

        if ('all' !== $user) {
            $qb->leftJoin('a.user', 'u');
            $qb->andWhere('u.username = :user')->setParameter('user', $user);
        }

        if ('all' !== $name) {
            $qb->andWhere('a.name = :name')->setParameter('name', $name);
        }

        if ('all' !== $token) {
            $qb->andWhere('a.token = :token')->setParameter('token', $token);
        }

        /*
        if ('all' !== $tickets) {
            $qb->leftJoin('a.tickets', 't');
            $qb->andWhere('t.content = :content')->setParameter('tickets', $tickets);
        }
        */

        if ('all' !== $roles) {
            $qb->andWhere('a.roles = :roles')->setParameter('roles', $roles);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        if ($sort) {
            $qb->orderBy('a.'.$sort, $order);
        }

        return $qb;
    }
}
