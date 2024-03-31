<?php

namespace App\DataFixtures;

use App\Entity\Recipe;
use App\Entity\Review;
use App\Entity\Comment;
use App\Entity\Testimonial;
use App\DataFixtures\FakerTrait;
use App\Entity\Ingredient;
use App\Entity\Quantity;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use FakerRestaurant\Provider\fr_FR\Restaurant;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
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
        $this->faker()->addProvider(new Restaurant($this->faker()));

        $ingredients = array_map(fn(string $name) => (new Ingredient()) 
            ->setName($name)
            ->setSlug(strtolower($this->slugger->slug($name))), [
            "Farine",
            "Sucre",
            "Oeufs",
            "Beurre",
            "Lait",
            "Levure chimique",
            "Sel",
            "Chocolat noir",
            "Pépites de chocolat",
            "Fruits secs (amandes, noix, etc...)",
            "Vanille",
            "Cannelle",
            "Fraise",
            "Banane",
            "Pomme",
            "Carotte",
            "Oignon",
            "Ail",
            "Échalote",
            "Herbes fraîches (ciboulette, persil, etc...)"
        ]);

        foreach ($ingredients as $ingredient) {
            $manager->persist($ingredient);
        }

        $units = [
            'gramme' => 'grammes',
            'milligramme' => 'milligrammes',
            'kg' => 'kg',
            'pincée' => 'pincées',
            'poignée' => 'poignées',
            'tasse' => 'tasses',
            'litre' => 'litres',
            'centilitre' => 'centilitres',
            'millilitre' => 'millilitres',
            'cuillère à soupe' => 'cuillères à soupe',
            'cuillère à café' => 'cuillères à café',
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
                ->setEnablereviews($this->faker()->numberBetween(0, 1))
                ->setIsOnline($this->faker()->numberBetween(0, 1))
            ;

            $category = $this->getReference('cat-' . $this->faker()->numberBetween(1, 4));
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
            AppAdminTeamUserFixtures::class
        ];
    }
}
