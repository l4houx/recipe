<?php

namespace App\DataFixtures;

use App\Entity\Venue;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AppVenueFixtures extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create 20 Venues
        $venues = [];
        for ($i = 0; $i <= 20; ++$i) {
            $venue = new Venue();
            $venue
                ->setName($this->faker()->unique()->sentence())
                ->setSlug($this->slugger->slug($venue->getName())->lower())
                ->setDescription($this->faker()->paragraphs(10, true))
                ->setPricing(mt_rand(0, 1) === 1 ? $this->faker()->numberBetween(10, 150) : null)
                ->setAvailibility(mt_rand(0, 1) === 1 ? $this->faker()->word() : null)
                ->setNeighborhoods(mt_rand(0, 1) === 1 ? $this->faker()->streetName() : null)
                ->setStreet($this->faker()->streetAddress())
                ->setStreet2($this->faker()->secondaryAddress())
                ->setCity($this->faker()->city())
                ->setState($this->faker()->region())
                ->setPostalcode($this->faker()->postcode())
                ->setLat($this->faker()->latitude())
                ->setLng($this->faker()->longitude())
                ->setIsListedondirectory($this->faker()->numberBetween(0, 1))
                ->setContactemail($this->faker()->email())
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                ->setIsShowmap($this->faker()->numberBetween(0, 1))
                ->setIsQuoteform($this->faker()->numberBetween(0, 1))
            ;

            $venuetype = $this->getReference('venuetype-' . $this->faker()->numberBetween(1, 19));
            $venue->setType($venuetype);

            //$restaurant = $this->getReference('restaurant-' . $this->faker()->numberBetween(1, 20));
            //$venue->setRestaurant($restaurant);

            $manager->persist($venue);
            $venues[] = $venue;
        }

        $manager->flush();
    }

    /**
     * @return array<array-key, class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [
            AppVenueTypeFixtures::class,
            //AppRestaurantFixtures::class
        ];
    }
}
