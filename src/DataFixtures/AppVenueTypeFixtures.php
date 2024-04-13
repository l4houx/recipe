<?php

namespace App\DataFixtures;

use App\Entity\VenueType;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppVenueTypeFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create of 19 Venue Type
        $this->createVenueType('Banquet Hall', true, $manager);
        $this->createVenueType('Bar', true, $manager);
        $this->createVenueType('Boat', true, $manager);
        $this->createVenueType('Brewery', true, $manager);
        $this->createVenueType('Cafe', true, $manager);
        $this->createVenueType('Co-working space', true, $manager);
        $this->createVenueType('Conference center', true, $manager);
        $this->createVenueType('Country Club', true, $manager);
        $this->createVenueType('Event Space', true, $manager);
        $this->createVenueType('Gallery', true, $manager);
        $this->createVenueType('Gym', true, $manager);
        $this->createVenueType('Hotel', true, $manager);
        $this->createVenueType('Loft', true, $manager);
        $this->createVenueType('Meeting space', true, $manager);
        $this->createVenueType('Museum', true, $manager);
        $this->createVenueType('Restaurant', true, $manager);
        $this->createVenueType('Stadium', true, $manager);
        $this->createVenueType('Theater', true, $manager);
        $this->createVenueType('Other', true, $manager);

        $manager->flush();
    }

    public function createVenueType(
        string $name,
        bool $isOnline,
        ObjectManager $manager
    ) {
        $venuetype = (new VenueType());
        $venuetype
            ->setName($name)
            ->setSlug($this->slugger->slug($venuetype->getName())->lower())
            ->setIsOnline($isOnline)
        ;
        $manager->persist($venuetype);

        $this->addReference('venuetype-' . $this->counter, $venuetype);
        ++$this->counter;

        return $venuetype;
    }
}
