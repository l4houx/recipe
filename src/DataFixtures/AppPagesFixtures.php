<?php

namespace App\DataFixtures;

use App\Entity\Setting\Page;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppPagesFixtures extends Fixture
{
    use FakerTrait;

    public function load(ObjectManager $manager): void
    {
        /** @var string $content */
        $content = $this->getContentMarkdown();

        /** @var int $views */
        $views = $this->faker()->numberBetween(0, 50);

        $pages = [
            1 => [
                'title' => 'Terms',
                'slug' => 'terms-of-service',
                'content' => $content,
                'views' => $views,
            ],
            2 => [
                'title' => 'Privacy',
                'slug' => 'privacy-policy',
                'content' => $content,
                'views' => $views,
            ],
            3 => [
                'title' => 'Cookie',
                'slug' => 'cookie-policy',
                'content' => $content,
                'views' => $views,
            ],
            4 => [
                'title' => 'GDPR compliance',
                'slug' => 'gdpr-compliance',
                'content' => $content,
                'views' => $views,
            ],
            5 => [
                'title' => 'About us',
                'slug' => 'about-us',
                'content' => $content,
                'views' => $views,
            ],
            6 => [
                'title' => 'Feedback',
                'slug' => 'feedback',
                'content' => $content,
                'views' => $views,
            ],
            7 => [
                'title' => 'Support',
                'slug' => 'support',
                'content' => $content,
                'views' => $views,
            ],
            8 => [
                'title' => 'Affiliates',
                'slug' => 'affiliates',
                'content' => $content,
                'views' => $views,
            ],
            9 => [
                'title' => 'Free Exchanges',
                'slug' => 'free_exchanges',
                'content' => $content,
                'views' => $views,
            ],
            10 => [
                'title' => 'Pricing and fees',
                'slug' => 'pricing-and-fees',
                'content' => $content,
                'views' => $views,
            ],
        ];

        // Create 10 Pages
        foreach ($pages as $key => $value) {
            $page = (new Page())
                ->setTitle($value['title'])
                ->setSlug($value['slug'])
                ->setContent($value['content'])
                ->setViews($value['views'])
            ;

            $manager->persist($page);
        }

        $manager->flush();
    }

    private function getContentMarkdown(): string
    {
        return <<<'MARKDOWN'
            <h1 class="fw-bold mb-3">This is a H1, Perfect's for titles.</h1>
            <p class="fs-4 mb-4">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Stress, for the United States element ante.
                Duis cursus, mi quis viverra ornare, eros pain, sometimes none at all, freedom of
                the living creature was as the profit and financial security. Jasmine neck adapter and just running
                it lorem makeup sad smile of the television set.
            </p>
            <p class="mb-1 fs-4">
                <span class="text-dark fw-semibold">Email:</span>
                hello@yourdomain.com
            </p>
            <p class="mb-1 fs-4">
                <span class="text-dark fw-semibold">Address:</span>
                52, Komal Villas, Mansarovar Vadodara - 374321
            </p>
            <div class="d-flex mt-5">
                <div>
                    <h3 class="fw-bold">A</h3>
                </div>
                <div class="ms-3">
                    <h3 class="fw-bold">This is a H3's perfect for the titles.</h3>
                    <p class="fs-4">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Stress, for the United States
                        element ante. Duis cursus, mi quis viverra ornare, eros pain, none at all, freedom of the
                        living creature was as the profit and financial security. Jasmine neck adapter and just
                        running it lorem makeup hairstyle. Now sad smile of the television set.
                    </p>
                </div>
            </div>
            <div class="d-flex mt-3">
                <div>
                    <h3 class="fw-bold">B</h3>
                </div>
                <div class="ms-3">
                    <h3 class="fw-bold">This is a H3's perfect for the titles.</h3>
                    <p class="fs-4">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Stress, for the United States
                        element ante. Duis cursus, mi quis viverra ornare, eros pain, none at all, freedom of the
                        living creature was as the profit and financial security. Jasmine neck adapter and just
                        running it lorem makeup hairstyle. Now sad smile of the television set.
                    </p>
                </div>
            </div>
            <div class="mt-5">
                <h2 class="fw-bold">This is a H2's perfect for the titles.</h2>
                <p class="fs-4">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Stress, for the United States element
                    ante. Duis cursus, mi quis viverra ornare, eros pain , sometimes none at all, freedom
                    of the living creature was as the profit and financial security. Jasmine neck adapter and just
                    running it lorem makeup hairstyle. Now sad smile of the television set.
                </p>
                <ul class="fs-4">
                    <li>More than 60+ components</li>
                    <li>Five ready tests</li>
                    <li>Coming soon page</li>
                    <li>Check list with left icon</li>
                    <li>And much more ...</li>
                </ul>
            </div>
            <div class="mt-5">
                <h2 class="fw-bold">This is a H2's perfect for the titles.</h2>
                <p class="fs-4">
                    Geeks ui takes the privacy of its users very seriously. For the current our Privacy Policy,
                    please click
                    <a href="#">here</a>
                    .
                </p>
                <p class="mb-6 fs-4">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis cursus, mi quis viverra ornare,
                    eros pain, sometimes none at all, freedom of the living creature was as the profit and
                    financial security. Jasmine neck adapter and just running it lorem makeup hairstyle. Now sad
                    smile of the television set.
                </p>
                <h2 class="fw-bold">Changes about terms</h2>
                <p class="fs-4">If we change our terms of use we will post those changes on this page. Registered
                    users will be sent an email that outlines changes made to the terms of use.</p>
                <p class="fs-4">
                    Questions? Please email us at
                    <a href="#">hello@yourdomain.com</a>
                </p>
            </div>
            MARKDOWN;
    }
}
