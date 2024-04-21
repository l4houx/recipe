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
                'border' => 'dark-warning',
                'btn' => 'outline-dark-warning',
                'btntitle' => 'Get Started for Free',
                'title' => 'Free',
                'subtitle' => 'Free account <span class="text-dark fw-medium">1</span> month',
                'price' => 0,
                'pricetitle' => "It's Free",
                'duration' => 1,
                'monthly' => 'Monthly',
                'stripe_id' => null,
            ],
            2 => [
                'border' => 'dark-primary',
                'btn' => 'primary',
                'btntitle' => 'Start Today',
                'title' => 'Starter',
                'subtitle' => 'Starter account <span class="text-dark fw-medium">1</span> month',
                'price' => 5,
                'pricetitle' => null,
                'duration' => 1,
                'monthly' => 'Monthly',
                'stripe_id' => null,
            ],
            3 => [
                'border' => 'dark-info',
                'btn' => 'outline-dark-info',
                'btntitle' => 'Contact Sales',
                'title' => 'Premium',
                'subtitle' => 'Premium account <span class="text-primary fw-medium">12</span> months.',
                'price' => 50,
                'pricetitle' => null,
                'duration' => 12,
                'monthly' => 'Yearly',
                'stripe_id' => null,
            ],
        ];

        foreach ($pricings as $key => $value) {
            $pricing = (new Pricing())
                ->setBorder($value['border'])
                ->setBtn($value['btn'])
                ->setBtnTitle($value['btntitle'])
                ->setTitle($value['title'])
                ->setSubTitle($value['subtitle'])
                ->setPrice((float)$value['price'])
                ->setPriceTitle($value['pricetitle'])
                ->setDuration((int)$value['duration'])
                ->setMonthly($value['monthly'])
                ->setStripeId($value['stripe_id'])
            ;
            $manager->persist($pricing);
        }

        $manager->flush();
    }
}