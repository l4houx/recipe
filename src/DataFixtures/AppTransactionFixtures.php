<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\FakerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppTransactionFixtures extends Fixture
{
    use FakerTrait;

    public function load(ObjectManager $manager): void
    {


        $manager->flush();
    }
}
