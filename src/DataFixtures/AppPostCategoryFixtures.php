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
        // Create of 8 Categories
        $this->createCategory('Plats', '#3f7fca', true, $manager);
        $this->createCategory('Entrees', '#1e81b0', true, $manager);
        $this->createCategory('Desserts', '#9141ac', false, $manager);
        $this->createCategory('Goûters', '#21130d', true, $manager);
        $this->createCategory('Bases', '#063970', true, $manager);
        $this->createCategory('Boissons', '#154c79', false, $manager);
        $this->createCategory('Apéritif', '#e07b39', true, $manager);
        $this->createCategory('Autres', '#154c79', true, $manager);

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

        $this->addReference('category-' . $this->counter, $category);
        ++$this->counter;

        return $category;
    }
}
