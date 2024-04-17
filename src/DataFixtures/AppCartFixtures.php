<?php

namespace App\DataFixtures;

use App\DataFixtures\FakerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppCartFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function load(ObjectManager $manager): void
    {


        $manager->flush();
    }
}
