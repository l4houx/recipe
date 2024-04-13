<?php

namespace App\DataFixtures;

use App\Entity\Recipe;
use App\Entity\Review;
use App\Entity\Comment;
use App\Entity\Quantity;
use App\Entity\Ingredient;
use App\Entity\Restaurant;
use App\Entity\Testimonial;
use App\DataFixtures\FakerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use FakerRestaurant\Provider\fr_FR\Restaurant as FakerRestaurant;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppRecipeFixtures extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->faker()->addProvider(new FakerRestaurant($this->faker()));

        $ingredients = array_map(fn(string $name) => (new Ingredient()) 
            ->setName($name)
            ->setSlug(strtolower($this->slugger->slug($name))), [
            "Flour",
            "Sugar",
            "Eggs",
            "Butter",
            "Milk",
            "Baking yeast",
            "Salt",
            "Dark chocolate",
            "Chocolate chips",
            "Dried fruits (almonds, walnuts, etc.)",
            "Vanilla",
            "Cinnamon",
            "Strawberry",
            "Banana",
            "Apple",
            "Carrot",
            "Onion",
            "Garlic",
            "Shallot",
            "Fresh herbs (chives, parsley, etc.)"
        ]);

        foreach ($ingredients as $ingredient) {
            $manager->persist($ingredient);
        }

        $units = [
            'gram' => 'grams',
            'milligram' => 'milligrams',
            'kg' => 'kg',
            'pinch' => 'pinches',
            'handle' => 'handles',
            'cup' => 'cups',
            'litre' => 'litres',
            'centiliter' => 'centiliters',
            'milliliter' => 'milliliters',
            'tablespoon' => 'tablespoons',
            'teaspoon' => 'teaspoons',
        ];

        // Create 20 Recipes by User and admin
        $recipes = [];
        for ($i = 0; $i <= 20; ++$i) {
            $recipe = new Recipe();
            $recipe
                ->setTitle($this->faker()->foodName())
                ->setSlug($this->slugger->slug($recipe->getTitle())->lower())
                ->setContent($this->faker()->paragraphs(10, true))
                ->setDuration($this->faker()->numberBetween(2, 60))
                ->setViews(rand(10, 160))
                ->setExternallink(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setWebsite(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setEmail(mt_rand(0, 1) === 1 ? $this->faker()->email() : null)
                ->setPhone(mt_rand(0, 1) === 1 ? $this->faker()->phoneNumber() : null)
                ->setYoutubeurl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setTwitterUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setInstagramUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setFacebookUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setGoogleplusUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setLinkedinUrl(mt_rand(0, 1) === 1 ? $this->faker()->url() : null)
                ->setAuthors(mt_rand(0, 1) === 1 ? $this->faker()->userName() : null)
                ->setYear(mt_rand(0, 1) === 1 ? $this->faker()->date('Y') : null)
                ->setTags(mt_rand(0, 1) === 1 ? $this->faker()->unique()->word() : null)
                ->setEnablereviews($this->faker()->numberBetween(0, 1))
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                ->setIsShowattendees($this->faker()->numberBetween(0, 1))
            ;

            $category = $this->getReference('cat-' . $this->faker()->numberBetween(1, 8));
            $recipe->setCategory($category);

            foreach ($this->faker()->randomElements($ingredients, $this->faker()->numberBetween(2, 5)) as $ingredient) {
                $recipe->addQuantity((new Quantity())
                    ->setQuantity($this->faker()->numberBetween(1, 250))
                    ->setUnit($this->faker()->randomElement($units))
                    ->setIngredient($ingredient)
                );
            }

            $manager->persist($recipe);

            $recipes[] = $recipe;
        }

        // Create 10 Testimonial by User
        for ($i = 0; $i <= 10; ++$i) {
            $testimonial = new Testimonial();
            $testimonial
                ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                ->setHeadline($this->faker()->unique()->sentence())
                ->setSlug($this->slugger->slug($testimonial->getHeadline())->lower())
                ->setContent($this->faker()->paragraph())
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                ->setRating($this->faker()->numberBetween(1, 5))
            ;

            $manager->persist($testimonial);
        }

        // Create 10 Review by Recipe
        for ($i = 0; $i <= 10; ++$i) {
            $review = new Review();
            $review
                ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                ->setRecipe($this->faker()->randomElement($recipes))
                ->setIsVisible($this->faker()->numberBetween(0, 1))
                ->setRating($this->faker()->numberBetween(1, 5))
                ->setHeadline($this->faker()->unique()->sentence())
                ->setSlug($this->slugger->slug($review->getHeadline())->lower())
                ->setContent($this->faker()->paragraph())
            ;

            $manager->persist($review);
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
            //AppRestaurantFixtures::class
        ];
    }
}
