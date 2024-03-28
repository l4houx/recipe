<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Traits\HasLimit;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Setting\HomepageHeroSetting;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * To get the aministrator.
     */
    public function getAdministrator()
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"ROLE_ADMIN_APPLICATION"%')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function findTeam(int $limit): array // (PagesController)
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.designation', 'DESC')
            ->andwhere('u.isVerified = :isVerified')
            ->andwhere('u.team = :team')
            ->setParameter('isVerified', true)
            ->setParameter('team', true)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Query used to retrieve a user for the login.
     */
    public function findUserByEmailOrUsername(string $usernameOrEmail): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('LOWER(u.email) = :identifier')
            // ->where('LOWER(u.email) = :identifier OR LOWER(u.username) = :identifier')
            // ->andWhere('u.isVerified = true')
            ->orWhere('LOWER(u.username) = :identifier')
            ->setParameter('identifier', mb_strtolower($usernameOrEmail))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findForPagination(int $page): PaginationInterface // UserController
    {
        $builder = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setParameter('now', new \DateTimeImmutable())
            ->where('u.createdAt <= :now')
        ;

        return $this->paginator->paginate(
            $builder->getQuery()->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                TranslationWalker::class
            ),
            $page,
            HasLimit::USER_LIMIT,
            [
                'distinct' => false,
                'sortFieldAllowList' => ['u.id'],
            ]
        );
    }

    /**
     * Returns the users after applying the specified search criterias.
     *
     * @param string                   $keyword
     * @param string                   $username
     * @param string                   $slug
     * @param string                   $email
     * @param string                   $firstname
     * @param string                   $lastname
     * @param bool                     $isVerified
     * @param HomepageHeroSetting|null $isOnHomepageSlider
     * @param int                      $limit
     * @param string                   $sort
     * @param string                   $order
     * @param int                      $count
     */
    public function getUsers($keyword, $username, $slug, $email, $firstname, $lastname, $isVerified, $isOnHomepageSlider, $limit, $sort, $order, $count): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');

        if ($count) {
            $qb->select('COUNT(u)');
        } else {
            $qb->select('u');
        }

        if ('all' !== $keyword) {
            $qb->andWhere('u.username LIKE :keyword or :keyword LIKE u.username or u.email LIKE :keyword or :keyword LIKE u.email or u.firstname LIKE :keyword or :keyword LIKE u.firstname or u.lastname LIKE :keyword or :keyword LIKE u.lastname')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ('all' !== $username) {
            $qb->andWhere('u.username = :username')->setParameter('username', $username);
        }

        if ('all' !== $slug) {
            $qb->andWhere('u.slug = :slug')->setParameter('slug', $slug);
        }

        if ('all' !== $email) {
            $qb->andWhere('u.email = :email')->setParameter('email', $email);
        }

        if ('all' !== $firstname) {
            $qb->andWhere('u.firstname LIKE :firstname or :firstname LIKE u.firstname')->setParameter('firstname', '%'.$firstname.'%');
        }

        if ('all' !== $lastname) {
            $qb->andWhere('u.lastname LIKE :lastname or :lastname LIKE u.lastname')->setParameter('lastname', '%'.$lastname.'%');
        }

        if ('all' !== $isVerified) {
            $qb->andWhere('u.isVerified = :isVerified')->setParameter('isVerified', $isVerified);
        }

        if (true === $isOnHomepageSlider) {
            $qb->andWhere('u.isuseronhomepageslider IS NOT NULL');
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy($sort, $order);

        $qb->andWhere('u.slug != :administrator')->setParameter('administrator', 'administrator');

        return $qb;
    }
}
