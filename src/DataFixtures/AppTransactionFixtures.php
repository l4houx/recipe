<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\DataFixtures\FakerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AppTransactionFixtures extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    public function load(ObjectManager $manager): void
    {
        // Create 20 Transactions
        $genres = ['male', 'female'];
        $genre = $this->faker()->randomElement($genres);
        $transactions = [];
        for ($i = 0; $i <= 20; ++$i) {
            $transaction = (new Transaction())
                ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                ->setDuration(rand(1))
                ->setPrice($this->faker()->numberBetween(5, 50))
                ->setTax(0.4)
                ->setFee(0.1)
                ->setMethod('paypal')
                ->setMethodRef($this->faker()->isbn13())
                ->setFirstname($this->faker()->lastName())
                ->setLastname($this->faker()->firstName($genre))
            ;

            $manager->persist($transaction);
            $transactions[] = $transaction;
        }

        $manager->flush();
    }

    /**
     * @return array<array-key, class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [
            AppAdminTeamUserFixtures::class,
        ];
    }
}
