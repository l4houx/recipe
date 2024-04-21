<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Level;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\Response;
use App\Entity\Application;
use App\DataFixtures\FakerTrait;
use App\Entity\Traits\HasRoles;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppTicketingFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function load(ObjectManager $manager): void
    {
        // Create Level
        $urgencyLevels = [];
        $urgencyLevel = new Level();
        $urgencyLevel->setName("Urgent")->setColor("#eb2f06");
        $manager->persist($urgencyLevel);
        $urgencyLevels[] = $urgencyLevel;

        $mediumLevels = [];
        $mediumLevel = new Level();
        $mediumLevel->setName("Medium")->setColor("#f6b93b");
        $manager->persist($mediumLevel);
        $mediumLevels[] = $mediumLevel;

        $lowLevels = [];
        $lowLevel = new Level();
        $lowLevel->setName("Low")->setColor("#4a69bd");
        $manager->persist($lowLevel);
        $lowLevels[] = $lowLevel;

        // Create Status
        $newStatus = [];
        $newStatu = new Status();
        $newStatu->setName("New")->setColor("#eb2f06");
        $manager->persist($newStatu);
        $newStatus[] = $newStatu;

        $openStatus = [];
        $openStatu = new Status();
        $openStatu->setName("Open")->setColor("#f6b93b");
        $manager->persist($openStatu);
        $openStatus[] = $openStatu;

        $closedStatus = [];
        $closedStatu = new Status();
        $closedStatu->setName("Closed")->setColor("#78e08f")->setIsClose(true);
        $manager->persist($closedStatu);
        $closedStatus[] = $closedStatu;

        // Create 10 Applications
        $applications = [];
        for ($i = 0; $i <= 10; ++$i) {
            $application = (new Application())
                ->setName($this->faker()->word())
                ->setToken($this->faker()->isbn13())
                ->setUser($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                ->setRoles([HasRoles::DEFAULT])
            ;

            $manager->persist($application);
            $applications[] = $application;
        }

        // Create 10 Tickets
        $tickets = [];
        for ($i = 0; $i <= 10; ++$i) {
            $ticket = (new Ticket())
                ->setContent($this->faker()->paragraphs(10, true))
                ->setSubject($this->faker()->word())
                ->setUser($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                ->setApplication($this->faker()->randomElement($applications))
                ->setStatus($this->faker()->randomElement($openStatus))
                ->setLevel($this->faker()->randomElement($lowLevels))
                ->setApiId($this->faker()->randomDigit())
                ->setAuthor($this->faker()->name())
            ;

            $manager->persist($ticket);
            $tickets[] = $ticket;
        }

        // Create 10 Responses
        $responses = [];
        for ($i = 0; $i <= 10; ++$i) {
            $response = (new Response())
                ->setApiId($this->faker()->randomDigit())
                ->setAuthor($this->faker()->name())
                ->setContent($this->faker()->paragraphs(10, true))
                ->setUser($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                ->setTicket($this->faker()->randomElement($tickets))
            ;

            $manager->persist($response);
            $responses[] = $response;
        }

        $manager->flush();
    }
}
