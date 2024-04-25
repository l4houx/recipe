<?php

namespace App\DataFixtures;

use App\Entity\Audience;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppAudienceFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create of 5 audiences
        $this->createAudience('Family', 'fas fa-people-roof', true, $manager);
        $this->createAudience('Group', 'fas fa-people-group', true, $manager);
        $this->createAudience('Youth', 'fas fa-people-pulling', true, $manager);
        $this->createAudience('Adults', 'fas fa-person', true, $manager);
        $this->createAudience('Children', 'fas fa-child', true, $manager);

        $manager->flush();
    }

    public function createAudience(
        string $name,
        string $icon,
        bool $isOnline,
        ObjectManager $manager
    ) {
        $audience = (new Audience());
        $audience
            ->setName($name)
            ->setSlug($this->slugger->slug($audience->getName())->lower())
            ->setIcon($icon)
            ->setIsOnline($isOnline)
        ;
        $manager->persist($audience);

        $this->addReference('audience-' . $this->counter, $audience);
        ++$this->counter;

        return $audience;
    }
}
