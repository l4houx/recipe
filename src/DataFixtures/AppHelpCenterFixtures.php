<?php

namespace App\DataFixtures;

use App\Entity\Faq;
use App\Entity\HelpCenterArticle;
use App\Entity\HelpCenterCategory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppHelpCenterFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    private int $subcategoryId = 0;

    /**
     * @var array<int, HelpCenterCategory>
     */
    private array $subcategories = [];

    public function __construct(
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->getHelpCenterCategoryData('User', null, 1, 'success', 'bi bi-person', $manager);
        $this->getHelpCenterCategoryData('Recipe', null, 1, 'primary', 'bi bi-book', $manager);

        $manager->flush();

        /** @var string $content */
        $content = $this->getContentMarkdown();

        for ($article = 1; $article <= 5; ++$article) {
            $helpcenterarticle = (new HelpCenterArticle());
            $helpcenterarticle
                ->setId($article)
                ->setTitle($this->faker()->unique()->sentence(2, true))
                ->setSlug($this->slugger->slug($helpcenterarticle->getTitle())->lower())
                ->setContent($content)
                ->setTags($this->faker()->unique()->words(3, true))
                ->setViews(rand(10, 160))
                ->setIsOnline($this->faker()->randomElement([true, false]))
                ->setIsFeatured($this->faker()->randomElement([true, false]))
                ->setCreatedAt(\DateTime::createFromInterface($this->faker()->dateTime()))
                ->setUpdatedAt(\DateTime::createFromInterface($this->faker()->dateTime()))
                ->setCategory($this->subcategories[$article % \count($this->subcategories)])
            ;

            $manager->persist($helpcenterarticle);
        }

        // Create 10 Faqs
        $faqs = [];
        for ($i = 0; $i <= 10; ++$i) {
            $faq = (new Faq())
                ->setQuestion($this->faker()->sentence)
                ->setAnswer($content)
                ->setIsOnline($this->faker()->randomElement([true, false]))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ;

            $manager->persist($faq);

            $faqs[] = $faq;
        }

        $manager->flush();
    }

    private function getHelpCenterCategoryData(
        string $name,
        HelpCenterCategory $parent = null,
        bool $isOnline,
        string $color = null,
        string $icon = null,
        ObjectManager $manager
    ): HelpCenterCategory {
        $helpcentercategory = (new HelpCenterCategory());
        $helpcentercategory
            ->setName($name)
            ->setSlug($this->slugger->slug($helpcentercategory->getName())->lower())
            ->setParent($parent)
            ->setIsOnline($isOnline)
            ->setColor($color)
            ->setIcon($icon)
            ->setCreatedAt(\DateTime::createFromInterface($this->faker()->dateTime()))
            ->setUpdatedAt(\DateTime::createFromInterface($this->faker()->dateTime()))
        ;
        $manager->persist($helpcentercategory);

        $this->addReference('cat-'.$this->counter, $helpcentercategory);
        ++$this->counter;

        return $helpcentercategory;
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
