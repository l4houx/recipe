<?php

namespace App\DataFixtures;

use App\Entity\Page;
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
                'slug' => 'terms',
                'content' => $content,
                'views' => $views,
            ],
            2 => [
                'title' => 'Privacy',
                'slug' => 'privacy',
                'content' => $content,
                'views' => $views,
            ],
            3 => [
                'title' => 'Cookie',
                'slug' => 'cookie',
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
                'title' => 'About',
                'slug' => 'about',
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
                'title' => 'Pricing',
                'slug' => 'pricing',
                'content' => $content,
                'views' => $views,
            ],
        ];

        // Create 9 Pages
        foreach ($pages as $key => $value) {
            $page = (new Page())
                ->setTitle($value['title'])
                ->setSlug($value['slug'])
                ->setContent($value['content'])
                ->setViews($value['views'])
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ;

            $manager->persist($page);
        }

        $manager->flush();
    }

    private function getContentMarkdown(): string
    {
        return <<<'MARKDOWN'
            Lorem ipsum dolor sit amet consectetur adipisicing elit, sed do eiusmod tempor
            incididunt ut labore et **dolore magna aliqua**: Duis aute irure dolor in
            reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia
            deserunt mollit anim id est laborum.

              * Ut enim ad minim veniam
              * Quis nostrud exercitation *ullamco laboris*
              * Nisi ut aliquip ex ea commodo consequat

            Praesent id fermentum lorem. Ut est lorem, fringilla at accumsan nec, euismod at
            nunc. Aenean mattis sollicitudin mattis. Nullam pulvinar vestibulum bibendum.
            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos
            himenaeos. Fusce nulla purus, gravida ac interdum ut, blandit eget ex. Duis a
            luctus dolor.

            Integer auctor massa maximus nulla scelerisque accumsan. *Aliquam ac malesuada*
            ex. Pellentesque tortor magna, vulputate eu vulputate ut, venenatis ac lectus.
            Praesent ut lacinia sem. Mauris a lectus eget felis mollis feugiat. Quisque
            efficitur, mi ut semper pulvinar, urna urna blandit massa, eget tincidunt augue
            nulla vitae est.

            Ut posuere aliquet tincidunt. Aliquam erat volutpat. **Class aptent taciti**
            sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Morbi
            arcu orci, gravida eget aliquam eu, suscipit et ante. Morbi vulputate metus vel
            ipsum finibus, ut dapibus massa feugiat. Vestibulum vel lobortis libero. Sed
            tincidunt tellus et viverra scelerisque. Pellentesque tincidunt cursus felis.
            Sed in egestas erat.

            Aliquam pulvinar interdum massa, vel ullamcorper ante consectetur eu. Vestibulum
            lacinia ac enim vel placerat. Integer pulvinar magna nec dui malesuada, nec
            congue nisl dictum. Donec mollis nisl tortor, at congue erat consequat a. Nam
            tempus elit porta, blandit elit vel, viverra lorem. Sed sit amet tellus
            tincidunt, faucibus nisl in, aliquet libero.
            MARKDOWN;
    }
}
