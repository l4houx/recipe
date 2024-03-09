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

        //$users = $this->userRepository->findAll();

        /*
        $categories = ['Plat chaud', 'Dessert', 'Entrée', 'Goûter'];
        foreach ($categories as $cat) {
            $category = (new Category())
                ->setName($cat)
                ->setSlug($this->slugger->slug($cat))
                ->setColor($this->faker()->hexColor())
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()));
            $manager->persist($category);
            $this->addReference($cat, $category);
        }
        */

        /*
        // Create 1 User Admin
        $admins = [];

        // User Admin
        /** @var User $admin /
        $admin = (new User());
        $admin
            ->setRoles([HasRoles::ADMIN])
            ->setLastname('Anne')
            ->setFirstname('Carlier')
            ->setUsername('anne-carlier')
            ->setSlug('anne-carlier')
            ->setEmail('anne-carlier@yourdomain.com')
            //->setPhone($this->faker()->phoneNumber())
            ->setIsTeam(true)
            ->setIsVerified(true)
            ->setAbout($this->faker()->realText(254))
            ->setDesignation('Admin Staff')
            ->setLastLogin(new \DateTimeImmutable())
            ->setLastLoginIp($this->faker()->ipv4())
            ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()));

        $manager->persist(
            $admin->setPassword(
                $this->hasher->hashPassword($admin, 'admin')
            )
        );
        $admins[] = $admin;
        */

        // Create 20 Recipes by User and admin
        $recipes = [];
        /*foreach ($users as $user) {*/
            for ($i = 0; $i <= 20; ++$i) {
                $recipe = new Recipe();
                $recipe
                    ->setTitle($this->faker()->foodName())
                    ->setSlug($this->slugger->slug($recipe->getTitle())->lower())
                    ->setContent($this->faker()->paragraphs(10, true))
                    ->setDuration($this->faker()->numberBetween(2, 60))
                    ->setViews(rand(10, 160))
                    ->setEnablereviews($this->faker()->numberBetween(0, 1))
                    ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                    ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                    //->setAuthor($this->faker()->boolean(50) ? $user : $admin)
                    ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                    ->setIsOnline($this->faker()->numberBetween(0, 1))
                    //->setCategory($this->getReference($this->faker()->randomElement($categories)));
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

                // Create Comments
                for ($k = 1; $k <= $this->faker()->numberBetween(1, 5); ++$k) {
                    $comment = new Comment();
                    $comment
                        //->setAuthor($this->faker()->boolean(50) ? $user : $admin)
                        ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                        ->setContent($this->faker()->paragraph())
                        ->setIsApproved($this->faker()->numberBetween(0, 1))
                        //->setIsReply()
                        //->setIsRGPD(true)
                        ->setIp($this->faker()->ipv4)
                        ->setRecipe($recipe)
                        ->setPublishedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                    ;

                    $manager->persist($comment);
                }
            }
        /*}*/

        // Create 10 Testimonial by User
        /*foreach ($users as $user) {*/
            for ($i = 0; $i <= 10; ++$i) {
                $testimonial = new Testimonial();
                $testimonial
                    //->setAuthor($user)
                    ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                    ->setContent($this->faker()->paragraph())
                    ->setIsOnline($this->faker()->numberBetween(0, 1))
                    ->setRating($this->faker()->numberBetween(1, 5))
                    ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                    ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ;

                $manager->persist($testimonial);
            }
        /*}*/

        // Create 10 Review by Recipe
        /*foreach ($users as $user) {*/
            for ($i = 0; $i <= 10; ++$i) {
                $review = new Review();
                $review
                    //->setAuthor($user)
                    ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                    ->setRecipe($this->faker()->randomElement($recipes))
                    ->setIsVisible($this->faker()->numberBetween(0, 1))
                    ->setRating($this->faker()->numberBetween(1, 5))
                    ->setHeadline($this->faker()->unique()->sentence())
                    ->setSlug($this->slugger->slug($review->getHeadline())->lower())
                    ->setContent($this->faker()->paragraph())
                    ->setCreatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                    ->setUpdatedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ;

                $manager->persist($review);
            }
        /*}*/

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
