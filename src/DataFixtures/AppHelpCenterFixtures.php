<?php

namespace App\DataFixtures;

use App\Entity\Faq;
use App\Entity\HelpCenterArticle;
use App\Entity\HelpCenterCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
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
        $this->getHelpCenterCategoryData('User', null, 1, '#78e08f', 'fas fa-user-alt', $manager);
        $this->getHelpCenterCategoryData('Restaurant', null, 1, '#4a69bd', 'fas fa-book', $manager);

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
                // ->setCategory($this->subcategories[$article % \count($this->subcategories)])
            ;

            $category = $this->getReference('helpcentercategory-'.$this->faker()->numberBetween(1, 2));
            $helpcenterarticle->setCategory($category);

            $manager->persist($helpcenterarticle);
        }

        // Create 10 Faqs
        $faqs = [];
        for ($i = 0; $i <= 10; ++$i) {
            $faq = (new Faq())
                ->setQuestion($this->faker()->sentence)
                ->setAnswer($content)
                ->setIsOnline($this->faker()->randomElement([true, false]))
            ;

            $manager->persist($faq);

            $faqs[] = $faq;
        }

        $manager->flush();
    }

    private function getHelpCenterCategoryData(
        string $name,
        ?HelpCenterCategory $parent,
        bool $isOnline,
        ?string $color,
        ?string $icon,
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
        ;
        $manager->persist($helpcentercategory);

        $this->addReference('helpcentercategory-'.$this->counter, $helpcentercategory);
        ++$this->counter;

        return $helpcentercategory;
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
                    Yourdomain ui takes the privacy of its users very seriously. For the current our Privacy Policy,
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
