<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AppRestaurantFixtures extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /*
        // Create 20 Restaurants
        $restaurants = [];
        for ($i = 0; $i <= 20; ++$i) {
            $restaurant = new Restaurant();
            $restaurant
                ->setName($this->faker()->unique()->sentence())
                ->setSlug($this->slugger->slug($restaurant->getName())->lower())
                ->setContent(mt_rand(0, 1) === 1 ? $this->faker()->paragraphs(10, true) : null)
                ->setViews(rand(10, 160))
                ->setExternallink(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setWebsite(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setEmail(mt_rand(0, 1) === 1 ? $this->faker()->email() : null)
                ->setPhone(mt_rand(0, 1) === 1 ? $this->faker()->phoneNumber() : null)
                ->setYoutubeurl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setTwitterUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setInstagramUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setFacebookUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setGoogleplusUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setLinkedinUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setIsShowvenuesmap($this->faker()->numberBetween(0, 1))
                ->setIsShowfollowers($this->faker()->numberBetween(0, 1))
                ->setIsShowreviews($this->faker()->numberBetween(0, 1))
                ->setAllowTapToCheckInOnScannerApp($this->faker()->numberBetween(0, 1))
                ->setShowRecipeDateStatsOnScannerApp($this->faker()->numberBetween(0, 1))
                ->setUser($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
            ;

            $manager->persist($restaurant);
            $restaurants[] = $restaurant;
        }
        */

        $manager->flush();
    }

    /**
     * @return array<array-key, class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [
            AppAdminTeamUserFixtures::class
        ];
    }
}
