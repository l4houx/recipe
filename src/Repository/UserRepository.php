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
     * Looking for an oauth user.
     */
    public function findForOauth(string $service, ?string $serviceId, ?string $email): ?User
    {
        if (null === $serviceId || null === $email) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->orWhere("u.{$service}Id = :serviceId")
            ->setMaxResults(1)
            ->setParameters([
                'email' => $email,
                'serviceId' => $serviceId,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
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
     * @return User[]
     */
    public function clean(): array
    {
        $query = $this->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->andWhere('u.deletedAt < NOW()')
        ;

        /** @var User[] $users */
        $users = $query->getQuery()->getResult();
        $query->delete(User::class, 'u')->getQuery()->execute();

        return $users;
    }

    /**
     * Returns the list of discord ids of premium members.
     *
     * @return string[]
     */
    public function findPremiumDiscordIds(): array
    {
        return array_map(fn (array $user) => $user['discordId'], $this->createQueryBuilder('u')
            ->where('u.discordId IS NOT NULL AND u.discordId <> \'\'')
            ->andWhere('u.premiumEnd > NOW()')
            ->select('u.discordId')
            ->getQuery()
            ->getResult())
        ;
    }

    /**
     * List suspended users
     */
    public function querySuspended(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->where('u.isSuspended IS NOT NULL')
            ->orderBy('u.isSuspended', 'DESC')
        ;
    }

    /**
     * Returns the users after applying the specified search criterias.
     *
     * @param string                   $role
     * @param string                   $keyword
     * @param string                   $createdbyrestaurantslug
     * @param string                   $restaurantname
     * @param string                   $restaurantslug
     * @param string                   $username
     * @param string                   $slug
     * @param string                   $followedby
     * @param string                   $email
     * @param string                   $firstname
     * @param string                   $lastname
     * @param bool                     $isVerified
     * @param bool                     $isSuspended
     * @param HomepageHeroSetting|null $isOnHomepageSlider
     * @param int                      $limit
     * @param string                   $sort
     * @param string                   $order
     * @param int                      $count
     */
    public function getUsers($role, $keyword, $createdbyrestaurantslug, $restaurantname, $restaurantslug, $username, $slug, $followedby, $email, $firstname, $lastname, $isVerified, $isSuspended, $isOnHomepageSlider, $limit, $sort, $order, $count): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');

        if ($count) {
            $qb->select('COUNT(u)');
        } else {
            $qb->select('u');
        }

        if ($role !== "all") {
            $qb->andWhere("u.roles LIKE :roles")->setParameter("roles", "%ROLE_" . strtoupper($role) . "%");
            if ($role == "pointofsale" && $createdbyrestaurantslug !== "all") {
                $qb->leftJoin("u.pointofsale", "pointofsale");
                $qb->leftJoin("pointofsale.restaurant", "pointofsalerestaurant");
                $qb->andWhere("pointofsalerestaurant.slug = :pointofsalecreatedbyrestaurantslug");
                $qb->setParameter("pointofsalecreatedbyrestaurantslug", $createdbyrestaurantslug);
            } else if ($role == "scanner" && $createdbyrestaurantslug !== "all") {
                $qb->leftJoin("u.scanner", "scanner");
                $qb->leftJoin("scanner.restaurant", "scannerrestaurant");
                $qb->andWhere("scannerrestaurant.slug = :scannercreatedbyrestaurantslug");
                $qb->setParameter("scannercreatedbyrestaurantslug", $createdbyrestaurantslug);
            }
        }

        if ('all' !== $keyword) {
            $qb->andWhere('u.username LIKE :keyword or :keyword LIKE u.username or u.email LIKE :keyword or :keyword LIKE u.email or u.firstname LIKE :keyword or :keyword LIKE u.firstname or u.lastname LIKE :keyword or :keyword LIKE u.lastname')->setParameter('keyword', '%'.$keyword.'%');
        }

        if ($restaurantname !== "all" || $restaurantslug || $followedby) {
            $qb->leftJoin("u.restaurant", "restaurant");
        }

        if ($restaurantname !== "all") {
            $qb->andWhere("restaurant.name LIKE :restaurantname or :restaurantname LIKE restaurant.name")->setParameter("restaurantname", "%" . $restaurantname . "%");
        }

        if ($restaurantslug !== "all") {
            $qb->andWhere("restaurant.slug = :restaurantslug")->setParameter("restaurantslug", $restaurantslug);
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

        if ('all' !== $isSuspended) {
            $qb->andWhere('u.isSuspended = :isSuspended')->setParameter('isSuspended', $isSuspended);
        }

        if (true === $isOnHomepageSlider) {
            $qb->andWhere('u.isrestaurantonhomepageslider IS NOT NULL');
        }

        if ($followedby !== "all") {
            $qb->andWhere(":followedby MEMBER OF restaurant.followedby")->setParameter("followedby", $followedby);
        }

        if ('all' !== $limit) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy($sort, $order);

        $qb->andWhere('u.slug != :administrator')->setParameter('administrator', 'administrator');

        return $qb;
    }
}
