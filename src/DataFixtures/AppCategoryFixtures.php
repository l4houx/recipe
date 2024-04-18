<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppCategoryFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create of 8 Categories
        $this->createCategory('Food', '#3f7fca', 'fab fa-foursquare', true, true, 1, $manager);
        $this->createCategory('Starters', '#f6b93b', 'fas fa-star', true, true, 2, $manager);
        $this->createCategory('Desserts', '#9141ac', 'fas fa-d', true, true, 3, $manager);
        $this->createCategory('Snacks', '#eb2f06', 'fas fa-s', true, true, 4, $manager);
        $this->createCategory('Bases', '#063970', 'fas fa-b', true, false, 5, $manager);
        $this->createCategory('Drinks', '#4a69bd', 'fas fa-d', true, false, 6, $manager);
        $this->createCategory('Appetizers', '#e07b39', 'fas fa-a', true, false, 7, $manager);
        $this->createCategory('Other', '#78e08f', 'fas fa-folder-open', true, false, 8, $manager);

        $manager->flush();
    }

    public function createCategory(
        string $name,
        ?string $color,
        ?string $icon,
        bool $isOnline,
        bool $isFeatured,
        ?int $featuredorder,
        ObjectManager $manager
    ) {
        $category = (new Category());
        $category
            ->setName($name)
            ->setSlug($this->slugger->slug($category->getName())->lower())
            ->setColor($color)
            ->setIcon($icon)
            ->setIsOnline($isOnline)
            ->setIsFeatured($isFeatured)
            ->setFeaturedorder($featuredorder)
        ;
        $manager->persist($category);

        $this->addReference('cat-' . $this->counter, $category);
        ++$this->counter;

        return $category;
    }
}
