<?php

namespace App\DataFixtures;

use App\Entity\Amenity;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppAmenityFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create of 13 amenities
        $this->createAmenity('Spa', 'fas fa-spa', true, $manager);
        $this->createAmenity('Beachfront', 'fas fa-umbrella-beach', true, $manager);
        $this->createAmenity('Business Center', 'fas fa-briefcase', true, $manager);
        $this->createAmenity('Handicap Accessible', 'fas fa-wheelchair', true, $manager);
        $this->createAmenity('Outdoor Space', 'fas fa-cloud-sun', true, $manager);
        $this->createAmenity('Pet Friendly', 'fas fa-dog', true, $manager);
        $this->createAmenity('WiFi', 'fas fa-wifi', true, $manager);
        $this->createAmenity('A/V Equipement', 'fas fa-volume-up', true, $manager);
        $this->createAmenity('Breakout rooms', 'fas fa-chair', true, $manager);
        $this->createAmenity('Parking', 'fas fa-parking', true, $manager);
        $this->createAmenity('Media room', 'fas fa-desktop', true, $manager);
        $this->createAmenity('Rooftop', 'fas fa-city', true, $manager);
        $this->createAmenity('Theater space', 'fas fa-theater-masks', true, $manager);

        $manager->flush();
    }

    public function createAmenity(
        string $name,
        string $icon,
        bool $isOnline,
        ObjectManager $manager
    ) {
        $amenity = (new Amenity());
        $amenity
            ->setName($name)
            ->setSlug($this->slugger->slug($amenity->getName())->lower())
            ->setIcon($icon)
            ->setIsOnline($isOnline)
        ;
        $manager->persist($amenity);

        $this->addReference('amenity-' . $this->counter, $amenity);
        ++$this->counter;

        return $amenity;
    }
}
