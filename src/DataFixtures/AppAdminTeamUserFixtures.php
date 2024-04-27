<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Country;
use App\Entity\Scanner;
use App\Entity\PointOfSale;
use App\Entity\Traits\HasRoles;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Setting\HomepageHeroSetting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppAdminTeamUserFixtures extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<Country> $countries */
        $countries = $manager->getRepository(Country::class)->findAll();

        /** @var array<HomepageHeroSetting> $homepages */
        $homepages = $manager->getRepository(HomepageHeroSetting::class)->findAll();

        // Create 20 Scanner
        $scanners = [];
        for ($i = 0; $i <= 20; ++$i) {
            $scanner = (new Scanner())
                ->setName($this->faker()->name())
                //->setRestaurant($this->getReference('restaurant-'.$this->faker()->numberBetween(1, 20)))
                //->setUser($this->faker()->randomElement($users))
                //->addRecipedate($this->faker()->randomElement($recipedates))
            ;

            $this->addReference('scanner-'.$i, $scanner);

            $manager->persist($scanner);
            $scanners[] = $scanner;
        }

        // Create 20 Point Of Sale
        $pointofsales = [];
        for ($i = 0; $i <= 20; ++$i) {
            $pointofsale = (new PointOfSale())
                ->setName($this->faker()->name())
                //->setRestaurant($this->getReference('restaurant-'.$this->faker()->numberBetween(1, 20)))
                //->setUser($this->faker()->randomElement($users))
                //->addRecipedate($this->getReference('restaurant-'.$this->faker()->numberBetween(1, 20)))
            ;

            $this->addReference('pointofsale-'.$i, $pointofsale);

            $manager->persist($pointofsale);
            $pointofsales[] = $pointofsale;
        }

        // User Super Admin Application
        /** @var User $superadmin */
        $superadmin = (new User());
        $superadmin
            ->setId(1)
            ->setTeamName('superadmin.jpg')
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
            ->setBirthdate(\DateTime::createFromInterface($this->faker()->dateTime()))
            ->setStreet($this->faker()->streetAddress())
            ->setStreet2($this->faker()->secondaryAddress())
            ->setCity($this->faker()->city())
            ->setState($this->faker()->region())
            ->setPostalcode($this->faker()->postcode())
            ->setCountry($this->faker()->randomElement($countries))
        ;

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
            ->setTeamName('admin.jpg')
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
            ->setBirthdate(\DateTime::createFromInterface($this->faker()->dateTime()))
            ->setStreet($this->faker()->streetAddress())
            ->setStreet2($this->faker()->secondaryAddress())
            ->setCity($this->faker()->city())
            ->setState($this->faker()->region())
            ->setPostalcode($this->faker()->postcode())
            ->setCountry($this->faker()->randomElement($countries))
        ;

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
            ->setTeamName('moderator.jpg')
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
            ->setBirthdate(\DateTime::createFromInterface($this->faker()->dateTime()))
            ->setStreet($this->faker()->streetAddress())
            ->setStreet2($this->faker()->secondaryAddress())
            ->setCity($this->faker()->city())
            ->setState($this->faker()->region())
            ->setPostalcode($this->faker()->postcode())
            ->setCountry($this->faker()->randomElement($countries))
        ;

        $manager->persist(
            $moderator->setPassword(
                $this->hasher->hashPassword($moderator, 'moderator')
            )
        );

        // User Restaurants
        /** //@var User $restaurant */
        /*
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
            ->setBirthdate(\DateTime::createFromInterface($this->faker()->dateTime()))
            ->setStreet($this->faker()->streetAddress())
            ->setStreet2($this->faker()->secondaryAddress())
            ->setCity($this->faker()->city())
            ->setState($this->faker()->region())
            ->setPostalcode($this->faker()->postcode())
            ->setCountry($this->faker()->randomElement($countries))
        ;

        $manager->persist(
            $restaurant->setPassword(
                $this->hasher->hashPassword($restaurant, 'restaurant')
            )
        );
        */

        // Create 10 Users
        $genres = ['male', 'female'];
        $genre = $this->faker()->randomElement($genres);
        for ($i = 0; $i <= 10; ++$i) {
            /** @var User $user */
            $user = (new User());
            $user
                ->setRoles([HasRoles::CREATOR])
                ->setLastname($this->faker()->lastName)
                ->setFirstname($this->faker()->firstName($genre))
                ->setUsername($this->faker()->unique()->userName())
                ->setSlug($this->slugger->slug($user->getUsername())->lower())
                ->setEmail($this->faker()->email())
                ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ->setLastLoginIp($this->faker()->ipv4())
                ->setPhone($this->faker()->phoneNumber())
                ->setBirthdate(\DateTime::createFromInterface($this->faker()->dateTime()))
                ->setStreet($this->faker()->streetAddress())
                ->setStreet2($this->faker()->secondaryAddress())
                ->setCity($this->faker()->city())
                ->setState($this->faker()->region())
                ->setPostalcode($this->faker()->postcode())
                ->setCountry($this->faker()->randomElement($countries))
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

        // Create 20 Restauranter Users
        $genres = ['male', 'female'];
        $genre = $this->faker()->randomElement($genres);
        $restauranters = [];
        for ($i = 0; $i <= 20; ++$i) {
            /** @var User $restauranter */
            $restauranter = (new User());
            $restauranter
                ->setLastname($this->faker()->lastName)
                ->setFirstname($this->faker()->firstName($genre))
                ->setUsername($this->faker()->unique()->userName())
                ->setSlug($this->slugger->slug($restauranter->getUsername())->lower())
                ->setEmail($this->faker()->email())
                ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ->setLastLoginIp($this->faker()->ipv4())
                ->setPhone($this->faker()->phoneNumber())
                ->setRoles([HasRoles::RESTAURANT])
                ->setBirthdate(\DateTime::createFromInterface($this->faker()->dateTime()))
                ->setStreet($this->faker()->streetAddress())
                ->setStreet2($this->faker()->secondaryAddress())
                ->setCity($this->faker()->city())
                ->setState($this->faker()->region())
                ->setPostalcode($this->faker()->postcode())
                ->setCountry($this->faker()->randomElement($countries))
                ->setIsrestaurantonhomepageslider($this->faker()->randomElement($homepages))
            ;

            if ($i > 5) {
                $restauranter->setIsVerified(false);
                $restauranter->setIsSuspended($this->faker()->numberBetween(0, 1));
                $restauranter->setIsAgreeTerms($this->faker()->numberBetween(0, 1));
            } else {
                $restauranter->setIsVerified(true);
            }

            $this->addReference('restauranter-'.$i, $restauranter);

            /*
            $pointofsale = $this->getReference('pointofsale-'.$this->faker()->numberBetween(1, 20));
            $restauranter->setPointOfSale($pointofsale);

            $scanner = $this->getReference('scanner-'.$this->faker()->numberBetween(1, 20));
            $restauranter->setScanner($scanner);

            $restaurant = $this->getReference('restaurant-'.$this->faker()->numberBetween(1, 20));
            $restauranter->setRestaurant($restaurant);
            */

            $manager->persist(
                $restauranter->setPassword(
                    $this->hasher->hashPassword($restauranter, 'restauranter')
                )
            );
            $restauranters[] = $restauranter;
        }

        $manager->flush();
    }

    /**
     * @return array<array-key, class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [
            AppSettingsFixtures::class,
            AppCountryFixtures::class,
        ];
    }
}
