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
        // Create of 16 Categories
        $this->createCategory('Creators', '#3f7fca', true, $manager);
        $this->createCategory('Branding', '#1e81b0', true, $manager);
        $this->createCategory('Budgeting', '#9141ac', true, $manager);
        $this->createCategory('Catering', '#eb230d', true, $manager);
        $this->createCategory('Collaboration', '#063970', true, $manager);
        $this->createCategory('Community', '#154c79', true, $manager);
        $this->createCategory('Content', '#e07b39', true, $manager);
        $this->createCategory('Feature', '#f6b93b', true, $manager);
        $this->createCategory('News', '#eb2f06', true, $manager);
        $this->createCategory('Pricing', '#4a69bd', true, $manager);
        $this->createCategory('Marketing', '#78e08f', true, $manager);
        $this->createCategory('Social Media', '#78ec79', true, $manager);
        $this->createCategory('Sponsoring', '#eb2c79', true, $manager);
        $this->createCategory('Tips', '#e07c79', true, $manager);
        $this->createCategory('Planning', '#914c79', true, $manager);
        $this->createCategory('Other', '#f6bc79', true, $manager);

        $manager->flush();
    }

    public function createCategory(
        string $name,
        ?string $color,
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
