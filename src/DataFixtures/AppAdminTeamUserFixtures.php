<?php

namespace App\DataFixtures;

use App\Entity\Traits\HasRoles;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppAdminTeamUserFixtures extends Fixture
{
    use FakerTrait;

    public const ADMINAPPLICATION = 'ADMINAPPLICATION';
    public const ADMIN = 'ADMIN';
    public const MODERATOR = 'MODERATOR';
    public const RESTAURANT = 'RESTAURANT';

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // User Super Admin Application
        /** @var User $superadmin */
        $superadmin = (new User());
        $superadmin
            ->setId(1)
            ->setRoles([HasRoles::ADMINAPPLICATION])
            ->setLastname('Cameron')
            ->setFirstname('Williamson')
            ->setUsername('superadmin')
            ->setSlug('superadmin')
            ->setEmail('superadmin@yourdomain.com')
            ->setPhone($this->faker()->phoneNumber())
            ->setIsTeam(true)
            ->setIsVerified(true)
            ->setAbout($this->faker()->realText(254))
            ->setDesignation('Super Admin Staff')
            ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setLastLoginIp($this->faker()->ipv4())
        ;

        $this->addReference(self::ADMINAPPLICATION, $superadmin);

        $manager->persist(
            $superadmin->setPassword(
                $this->hasher->hashPassword($superadmin, 'superadmin')
            )
        );

        // User Admin
        /** @var User $admin */
        $admin = (new User());
        $admin
            ->setId(2)
            ->setRoles([HasRoles::ADMIN])
            ->setLastname('Wade')
            ->setFirstname('Warren')
            ->setUsername('admin')
            ->setSlug('admin')
            ->setEmail('admin@yourdomain.com')
            ->setPhone($this->faker()->phoneNumber())
            ->setIsTeam(true)
            ->setIsVerified(true)
            ->setAbout($this->faker()->realText(254))
            ->setDesignation('Admin Staff')
            ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setLastLoginIp($this->faker()->ipv4())
        ;

        $this->addReference(self::ADMIN, $admin);

        $manager->persist(
            $admin->setPassword(
                $this->hasher->hashPassword($admin, 'admin')
            )
        );

        // User Moderator
        /** @var User $moderator */
        $moderator = (new User());
        $moderator
            ->setId(3)
            ->setRoles([HasRoles::MODERATOR])
            ->setLastname('Jane')
            ->setFirstname('Cooper')
            ->setUsername('moderator')
            ->setSlug('moderator')
            ->setEmail('moderator@yourdomain.com')
            ->setPhone($this->faker()->phoneNumber())
            ->setIsTeam(true)
            ->setIsVerified(true)
            ->setAbout($this->faker()->realText(254))
            ->setDesignation('Moderator Staff')
            ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setLastLoginIp($this->faker()->ipv4())
        ;

        $this->addReference(self::MODERATOR, $moderator);

        $manager->persist(
            $moderator->setPassword(
                $this->hasher->hashPassword($moderator, 'moderator')
            )
        );

        // User Restaurants
        /** @var User $restaurant */
        $restaurant = (new User());
        $restaurant
            ->setId(4)
            ->setRoles([HasRoles::RESTAURANT])
            ->setLastname('Bob')
            ->setFirstname('Cooper')
            ->setUsername('restaurant')
            ->setSlug('restaurant')
            ->setEmail('restaurant@yourdomain.com')
            ->setPhone($this->faker()->phoneNumber())
            ->setIsVerified(true)
            ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setLastLoginIp($this->faker()->ipv4())
        ;

        $this->addReference(self::RESTAURANT, $restaurant);

        $manager->persist(
            $restaurant->setPassword(
                $this->hasher->hashPassword($restaurant, 'restaurant')
            )
        );

        // Create 10 Users
        $genres = ['male', 'female'];
        $genre = $this->faker()->randomElement($genres);
        for ($i = 0; $i <= 10; ++$i) {
            /** @var User $user */
            $user = (new User());
            $user
                ->setLastname($this->faker()->lastName)
                ->setFirstname($this->faker()->firstName($genre))
                ->setUsername($this->faker()->unique()->userName())
                ->setSlug($this->slugger->slug($user->getUsername())->lower())
                ->setEmail($this->faker()->email())
                ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ->setLastLoginIp($this->faker()->ipv4())
                ->setPhone($this->faker()->phoneNumber())
                ->addRole(HasRoles::CREATOR)
            ;

            if ($i > 5) {
                $user->setIsVerified(false);
                $user->setIsSuspended($this->faker()->numberBetween(0, 1));
                $user->setIsAgreeTerms($this->faker()->numberBetween(0, 1));
            } else {
                $user->setIsVerified(true);
            }

            $this->addReference('user-'.$i, $user);

            $manager->persist(
                $user->setPassword(
                    $this->hasher->hashPassword($user, 'user')
                )
            );
        }

        $manager->flush();
    }
}
