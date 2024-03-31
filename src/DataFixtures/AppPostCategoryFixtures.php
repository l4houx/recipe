<?php

namespace App\DataFixtures;

use App\Entity\PostCategory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
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
        // Create of 7 Categories
        $parent = $this->createCategory('Parent1', null, null, $manager);
        $this->createCategory('Plat chaud', '#3f7fca', $parent, $manager);
        $this->createCategory('Entrée', '#1e81b0', $parent, $manager);

        $parent = $this->createCategory('Parent2', null, null, $manager);
        $this->createCategory('Dessert', '#9141ac', $parent, $manager);
        $this->createCategory('Goûter', '#21130d', $parent, $manager);
        //$this->createCategory('', '#063970', $parent, $manager);
        //$this->createCategory('', '#154c79', $parent, $manager);
        //$this->createCategory('', '#e07b39', $parent, $manager);

        $manager->flush();
    }

    public function createCategory(
        string $name,
        string $color = null,
        PostCategory $parent = null,
        ObjectManager $manager
    ) {
        $category = (new PostCategory());
        $category
            ->setName($name)
            ->setSlug($this->slugger->slug($category->getName())->lower())
            ->setColor($color)
            ->setParent($parent)
        ;
        $manager->persist($category);

        //$this->addReference('category-' . $this->counter, $category);
        //++$this->counter;

        return $category;
    }
}
