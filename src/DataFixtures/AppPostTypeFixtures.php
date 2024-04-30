<?php

namespace App\DataFixtures;

use App\Entity\PostType;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppPostTypeFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create of 4 Post Type
        $this->createPostType('Image', true, $manager);
        $this->createPostType('Film', true, $manager);
        $this->createPostType('File', true, $manager);
        $this->createPostType('Link', true, $manager);

        $manager->flush();
    }

    public function createPostType(
        string $name,
        bool $isOnline,
        ObjectManager $manager
    ) {
        $type = (new PostType());
        $type
            ->setName($name)
            ->setSlug($this->slugger->slug($type->getName())->lower())
            ->setIsOnline($isOnline)
        ;
        $manager->persist($type);

        $this->addReference('type-' . $this->counter, $type);
        ++$this->counter;

        return $type;
    }
}
