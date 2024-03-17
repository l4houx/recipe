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
        // Create of 6 Categories
        $this->createCategory('Apéritifs', '#e07b39', $manager);
        $this->createCategory('Plats', '#3f7fca', $manager);
        $this->createCategory('Desserts', '#9141ac', $manager);
        $this->createCategory('Entrées', '#1e81b0', $manager);
        $this->createCategory('Boissons', '#21130d', $manager);
        $this->createCategory('Petit-déj/brunch', '#063970', $manager);
        //$this->createCategory('', '#154c79', $manager);

        $manager->flush();
    }

    public function createCategory(
        string $name,
        string $color = null,
        ObjectManager $manager
    ) {
        $category = (new Category());
        $category
            ->setName($name)
            ->setSlug($this->slugger->slug($category->getName())->lower())
            ->setColor($color)
            ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
        ;
        $manager->persist($category);

        $this->addReference('cat-' . $this->counter, $category);
        ++$this->counter;

        return $category;
    }
}
