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
        // Create of 6 Keywords
        $this->createKeyword('Apéritifs', '#e07b39', $manager);
        $this->createKeyword('Plats', '#3f7fca', $manager);
        $this->createKeyword('Desserts', '#9141ac', $manager);
        $this->createKeyword('Entrées', '#1e81b0', $manager);
        $this->createKeyword('Boissons', '#21130d', $manager);
        $this->createKeyword('Petit-déj/brunch', '#063970', $manager);
        //$this->createKeyword('', '#154c79', $manager);

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
            ->setIsOnline(true)
        ;
        $manager->persist($keyword);

        //$this->addReference('keyword-' . $this->counter, $keyword);
        //++$this->counter;

        return $keyword;
    }
}
