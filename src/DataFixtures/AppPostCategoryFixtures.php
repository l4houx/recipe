<?php

namespace App\DataFixtures;

use App\Entity\PostCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppPostCategoryFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create of 4 Categories
        $this->createCategory('Plat chaud', '#3f7fca', true, $manager);
        $this->createCategory('Entrée', '#1e81b0', true, $manager);
        $this->createCategory('Dessert', '#9141ac', false, $manager);
        $this->createCategory('Goûter', '#21130d', true, $manager);
        // $this->createCategory('', '#063970', $manager);
        // $this->createCategory('', '#154c79', $manager);
        // $this->createCategory('', '#e07b39', $manager);

        $manager->flush();
    }

    public function createCategory(
        string $name,
        ?string $color = null,
        bool $isOnline,
        ObjectManager $manager
    ) {
        $category = (new PostCategory());
        $category
            ->setName($name)
            ->setSlug($this->slugger->slug($category->getName())->lower())
            ->setColor($color)
            ->setIsOnline($isOnline)
        ;
        $manager->persist($category);

        return $category;
    }
}
