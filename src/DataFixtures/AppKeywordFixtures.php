<?php

namespace App\DataFixtures;

use App\Entity\Keyword;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppKeywordFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create of 7 Keywords
        $this->createKeyword('Plat chaud', '#3f7fca', $manager);
        $this->createKeyword('Entrée', '#1e81b0', $manager);
        $this->createKeyword('Dessert', '#9141ac', $manager);
        $this->createKeyword('Goûter', '#21130d', $manager);
        //$this->createKeyword('', '#063970', $manager);
        //$this->createKeyword('', '#154c79', $manager);
        //$this->createKeyword('', '#e07b39', $manager);

        $manager->flush();
    }

    public function createKeyword(
        string $name,
        string $color = null,
        ObjectManager $manager
    ) {
        $keyword = (new Keyword());
        $keyword
            ->setName($name)
            ->setSlug($this->slugger->slug($keyword->getName())->lower())
            ->setColor($color)
            ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
        ;
        $manager->persist($keyword);

        //$this->addReference('keyword-' . $this->counter, $keyword);
        //++$this->counter;

        return $keyword;
    }
}
