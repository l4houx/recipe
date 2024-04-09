<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Pricing;
use App\DataFixtures\FakerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppPricingFixtures extends Fixture
{
    use FakerTrait;

    public function load(ObjectManager $manager): void
    {
        $pricings = [
            1 => [
                'btn' => 'outline-primary',
                'imageName' => 'starter.svg',
                'title' => 'Starter',
                'subtitle' => 'Starter account 1 month',
                'symbol' => '$',
                'price' => 5,
                'duration' => 1,
                'monthly' => 'Monthly',
                'stripe_id' => 'price_1HfQctFCMNgisvowhVOcdXr1',
            ],
            2 => [
                'btn' => 'primary',
                'imageName' => 'premium.svg',
                'title' => 'Premium',
                'subtitle' => 'Premium account 12 months',
                'symbol' => '$',
                'price' => 50,
                'duration' => 12,
                'monthly' => 'Yearly',
                'stripe_id' => 'price_1HfQd2FCMNgisvowxxEQMysm',
            ],
        ];

        foreach ($pricings as $key => $value) {
            $pricing = (new Pricing())
                ->setBtn($value['btn'])
                ->setImageName($value['imageName'])
                ->setTitle($value['title'])
                ->setSubTitle($value['subtitle'])
                ->setSymbol($value['symbol'])
                ->setPrice($value['price'])
                ->setDuration($value['duration'])
                ->setMonthly($value['monthly'])
                ->setStripeId($value['stripe_id'])
            ;
            $manager->persist($pricing);
        }

        $manager->flush();
    }
}