<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Level;
use App\Entity\Status;
use App\DataFixtures\FakerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppTicketingFixtures extends Fixture
{
    use FakerTrait;

    public function load(ObjectManager $manager): void
    {
        $urgencyLevel = new Level();
        $urgencyLevel->setName("Urgent")->setColor("#eb2f06");
        $manager->persist($urgencyLevel);

        $mediumLevel = new Level();
        $mediumLevel->setName("Medium")->setColor("#f6b93b");
        $manager->persist($mediumLevel);

        $lowLevel = new Level();
        $lowLevel->setName("Low")->setColor("#4a69bd");
        $manager->persist($lowLevel);

        $newStatus = new Status();
        $newStatus->setName("New")->setColor("#eb2f06");
        $manager->persist($newStatus);

        $openStatus = new Status();
        $openStatus->setName("Open")->setColor("#f6b93b");
        $manager->persist($openStatus);

        $closedStatus = new Status();
        $closedStatus->setName("Closed")->setColor("#78e08f");
        $manager->persist($closedStatus);

        $manager->flush();
    }
}
