<?php

namespace App\DataFixtures;

use App\Entity\CartElement;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Country;
use App\Entity\Ingredient;
use App\Entity\PointOfSale;
use App\Entity\Quantity;
use App\Entity\Recipe;
use App\Entity\RecipeDate;
use App\Entity\RecipeImage;
use App\Entity\RecipeSubscription;
use App\Entity\Restaurant;
use App\Entity\Review;
use App\Entity\Scanner;
use App\Entity\Setting\HomepageHeroSetting;
use App\Entity\Setting\Language;
use App\Entity\Testimonial;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Entity\Venue;
use App\Entity\VenueImage;
use App\Entity\VenueSeatingPlan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use FakerRestaurant\Provider\fr_FR\Restaurant as FakerRestaurant;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppRecipeFixtures extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<Country> $countries */
        $countries = $manager->getRepository(Country::class)->findAll();

        /** @var array<Category> $categories */
        $categories = $manager->getRepository(Category::class)->findAll();

        /** @var array<Venue> $venues */
        $venues = $manager->getRepository(Venue::class)->findAll();

        /** @var array<Language> $languages */
        $languages = $manager->getRepository(Language::class)->findAll();

        /** @var array<Language> $subtitles */
        $subtitles = $manager->getRepository(Language::class)->findAll();

        /** @var array<HomepageHeroSetting> $homepages */
        $homepages = $manager->getRepository(HomepageHeroSetting::class)->findAll();

        /** @var array<User> $restauranters */
        $restauranters = $manager->getRepository(User::class)->findAll();

        // Provider Restaurant
        $this->faker()->addProvider(new FakerRestaurant($this->faker()));

        $ingredients = array_map(fn (string $name) => (new Ingredient())
            ->setName($name)
            ->setSlug(strtolower($this->slugger->slug($name))), [
                'Flour',
                'Sugar',
                'Eggs',
                'Butter',
                'Milk',
                'Baking yeast',
                'Salt',
                'Dark chocolate',
                'Chocolate chips',
                'Dried fruits (almonds, walnuts, etc.)',
                'Vanilla',
                'Cinnamon',
                'Strawberry',
                'Banana',
                'Apple',
                'Carrot',
                'Onion',
                'Garlic',
                'Shallot',
                'Fresh herbs (chives, parsley, etc.)',
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
                ->setDuration(rand(100, 5000))
                ->setViews(rand(10, 160))
                ->setExternallink(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setWebsite(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setEmail(1 === mt_rand(0, 1) ? $this->faker()->email() : null)
                ->setPhone(1 === mt_rand(0, 1) ? $this->faker()->phoneNumber() : null)
                ->setYoutubeurl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setTwitterUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setInstagramUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setFacebookUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setGoogleplusUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setLinkedinUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setAuthors(1 === mt_rand(0, 1) ? $this->faker()->userName() : null)
                ->setYear(1 === mt_rand(0, 1) ? $this->faker()->date('Y') : null)
                ->setTags(1 === mt_rand(0, 1) ? $this->faker()->unique()->word() : null)
                ->setEnablereviews($this->faker()->numberBetween(0, 1))
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                ->setIsShowattendees($this->faker()->numberBetween(0, 1))
                ->setCountry($this->faker()->randomElement($countries))
                ->addLanguage($this->faker()->randomElement($languages))
                ->addSubtitle($this->faker()->randomElement($subtitles))
                ->setIsonhomepageslider($this->faker()->randomElement($homepages))
                ->setLevel($this->faker()->numberBetween(0, 2))
                ->setPremium($this->faker()->numberBetween(0, 1))
            ;

            $category = $this->getReference('cat-'.$this->faker()->numberBetween(1, 8));
            $recipe->setCategory($category);

            $audience = $this->getReference('audience-'.$this->faker()->numberBetween(1, 5));
            $recipe->addAudience($audience);

            $addedtofavoritesby = $this->getReference('user-'.$this->faker()->numberBetween(1, 10));
            $recipe->addAddedtofavoritesby($addedtofavoritesby);

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

        // Create 20 Recipes Images
        $recipeimages = [];
        for ($i = 0; $i <= 20; ++$i) {
            $recipeimage = (new RecipeImage())
                ->setPosition(rand(1, 10))
                ->setRecipe($this->faker()->randomElement($recipes))
            ;

            $manager->persist($recipeimage);
            $recipeimages[] = $recipeimage;
        }

        // Create 20 Testimonial by User
        for ($i = 0; $i <= 20; ++$i) {
            $testimonial = new Testimonial();
            $testimonial
                ->setAuthor($this->getReference('user-'.$this->faker()->numberBetween(1, 10)))
                ->setHeadline($this->faker()->unique()->sentence(5, true))
                ->setSlug($this->slugger->slug($testimonial->getHeadline())->lower())
                ->setContent($this->faker()->paragraph())
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                ->setRating($this->faker()->numberBetween(1, 5))
            ;

            $manager->persist($testimonial);
        }

        // Create 20 Review by Recipe
        for ($i = 0; $i <= 20; ++$i) {
            $review = new Review();
            $review
                ->setAuthor($this->getReference('user-'.$this->faker()->numberBetween(1, 10)))
                ->setRecipe($this->faker()->randomElement($recipes))
                ->setIsVisible($this->faker()->numberBetween(0, 1))
                ->setRating($this->faker()->numberBetween(1, 5))
                ->setHeadline($this->faker()->unique()->sentence(5, true))
                ->setSlug($this->slugger->slug($review->getHeadline())->lower())
                ->setContent($this->faker()->paragraph())
            ;

            $manager->persist($review);
        }

        // Create 20 Restaurants
        $restaurants = [];
        for ($i = 0; $i <= 20; ++$i) {
            $restaurant = new Restaurant();
            $restaurant
                ->setName($this->faker()->sentence(5, true))
                ->setSlug($this->slugger->slug($restaurant->getName())->lower())
                ->setContent(1 === mt_rand(0, 1) ? $this->faker()->paragraphs(10, true) : null)
                ->setViews(rand(10, 160))
                ->setExternallink(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setWebsite(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setEmail(1 === mt_rand(0, 1) ? $this->faker()->email() : null)
                ->setPhone(1 === mt_rand(0, 1) ? $this->faker()->phoneNumber() : null)
                ->setYoutubeurl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setTwitterUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setInstagramUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setFacebookUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setGoogleplusUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setLinkedinUrl(1 === mt_rand(0, 1) ? $this->faker()->url() : null)
                ->setIsShowvenuesmap($this->faker()->numberBetween(0, 1))
                ->setIsShowfollowers($this->faker()->numberBetween(0, 1))
                ->setIsShowreviews($this->faker()->numberBetween(0, 1))
                ->setAllowTapToCheckInOnScannerApp($this->faker()->numberBetween(0, 1))
                ->setShowRecipeDateStatsOnScannerApp($this->faker()->numberBetween(0, 1))
                ->setCountry($this->faker()->randomElement($countries))
                ->addRecipe($this->faker()->randomElement($recipes))
                ->addCategory($this->faker()->randomElement($categories))
            ;

            $this->addReference('restaurant-'.$i, $restaurant);

            $followedby = $this->getReference('user-'.$this->faker()->numberBetween(1, 10));
            $restaurant->addFollowedby($followedby);

            $pointofsale = $this->getReference('pointofsale-'.$this->faker()->numberBetween(1, 20));
            $restaurant->addPointOfSale($pointofsale);

            $scanner = $this->getReference('scanner-'.$this->faker()->numberBetween(1, 20));
            $restaurant->addScanner($scanner);

            //$restauranter = $this->getReference('restauranter-'.$this->faker()->numberBetween(1, 20));
            //$restaurant->setUser($restauranter);

            $manager->persist($restaurant);
            $restaurants[] = $restaurant;
        }

        // Create 20 Venues
        $venues = [];
        for ($i = 0; $i <= 20; ++$i) {
            $venue = new Venue();
            $venue
                ->setName($this->faker()->unique()->sentence(5, true))
                ->setSlug($this->slugger->slug($venue->getName())->lower())
                ->setDescription($this->faker()->paragraphs(10, true))
                ->setPricing(1 === mt_rand(0, 1) ? $this->faker()->numberBetween(10, 250) : null)
                ->setAvailibility(1 === mt_rand(0, 1) ? $this->faker()->word() : null)
                ->setNeighborhoods(1 === mt_rand(0, 1) ? $this->faker()->streetName() : null)
                ->setStreet($this->faker()->streetAddress())
                ->setStreet2($this->faker()->secondaryAddress())
                ->setCity($this->faker()->city())
                ->setState($this->faker()->region())
                ->setPostalcode($this->faker()->postcode())
                ->setLat($this->faker()->latitude())
                ->setLng($this->faker()->longitude())
                ->setIsListedondirectory($this->faker()->numberBetween(0, 1))
                ->setContactemail($this->faker()->email())
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                ->setIsShowmap($this->faker()->numberBetween(0, 1))
                ->setIsQuoteform($this->faker()->numberBetween(0, 1))
                ->setRestaurant($this->faker()->randomElement($restaurants))
                ->setCountry($this->faker()->randomElement($countries))
            ;

            $venuetype = $this->getReference('venuetype-'.$this->faker()->numberBetween(1, 19));
            $venue->setType($venuetype);

            $amenity = $this->getReference('amenity-'.$this->faker()->numberBetween(1, 13));
            $venue->addAmenity($amenity);

            $manager->persist($venue);
            $venues[] = $venue;

            // Create Comments
            for ($k = 1; $k <= $this->faker()->numberBetween(1, 5); ++$k) {
                $comment = (new Comment())
                    ->setIp($this->faker()->ipv4)
                    ->setContent($this->faker()->paragraph())
                    ->setAuthor($this->getReference('user-'.$this->faker()->numberBetween(1, 10)))
                    ->setVenue($venue)
                    ->setParent(null)
                    ->setIsApproved($this->faker()->numberBetween(0, 1))
                    ->setIsRGPD(true)
                    ->setPublishedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ;

                $manager->persist($comment);
            }
        }

        // Create 20 Venues Images
        $venueimages = [];
        for ($i = 0; $i <= 20; ++$i) {
            $venueimage = (new VenueImage())
                ->setPosition(rand(1, 10))
                ->setVenue($this->faker()->randomElement($venues))
            ;

            $manager->persist($venueimage);
            $venueimages[] = $venueimage;
        }

        // Create 20 Venues Seating Plan
        $venueseatingplans = [];
        for ($i = 0; $i <= 20; ++$i) {
            $venueseatingplan = (new VenueSeatingPlan());
            $venueseatingplan
                ->setName($this->faker()->unique()->sentence())
                ->setSlug($this->slugger->slug($venueseatingplan->getName())->lower())
                // ->setDesign()
                ->setVenue($this->faker()->randomElement($venues))
            ;

            $manager->persist($venueseatingplan);
            $venueseatingplans[] = $venueseatingplan;
        }

        // Create 20 Recipe Date
        $recipedates = [];
        for ($i = 0; $i <= 20; ++$i) {
            $recipedate = (new RecipeDate())
                ->setReference($this->faker()->vat(false))
                ->setStartdate($this->faker()->dateTime())
                ->setEnddate($this->faker()->dateTime())
                ->setRecipe($this->faker()->randomElement($recipes))
                ->setVenue($this->faker()->randomElement($venues))
                ->setSeatingPlan($this->faker()->randomElement($venueseatingplans))
                ->setHasSeatingPlan($this->faker()->numberBetween(0, 1))
                ->setIsActive($this->faker()->numberBetween(0, 1))
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                // ->addPayoutRequest()
                ->addPointOfSale($this->getReference('pointofsale-'.$this->faker()->numberBetween(1, 20)))
                ->addScanner($this->getReference('scanner-'.$this->faker()->numberBetween(1, 20)))
                // ->addSubscription($this->getReference('recipesubscription-'.$this->faker()->numberBetween(1, 20)))
            ;

            $manager->persist($recipedate);
            $recipedates[] = $recipedate;
        }

        // Create 20 Recipe Subscription
        $recipesubscriptions = [];
        for ($i = 0; $i <= 20; ++$i) {
            $recipesubscription = (new RecipeSubscription())
                ->setReference($this->faker()->vat(false))
                ->setName($this->faker()->word())
                ->setDescription($this->faker()->paragraphs(10, true))
                ->setPrice(1 === mt_rand(0, 1) ? $this->faker()->numberBetween(10, 250) : null)
                ->setPromotionalPrice(1 === mt_rand(0, 1) ? $this->faker()->numberBetween(10, 250) : null)
                ->setQuantity(1 === mt_rand(0, 1) ? $this->faker()->numberBetween(10, 250) : null)
                ->setSubscriptionsperattendee(1 === mt_rand(0, 1) ? $this->faker()->numberBetween(10, 250) : null)
                ->setSalesstartdate($this->faker()->dateTime())
                ->setSalesenddate($this->faker()->dateTime())
                ->setPosition(rand(1, 10))
                // ->setReservedSeat()
                // ->setSeatingPlanSections()
                ->setRecipeDate($this->faker()->randomElement($recipedates))
                ->setIsFree($this->faker()->numberBetween(0, 1))
                ->setIsActive($this->faker()->numberBetween(0, 1))
                // ->addCartElement($this->getReference('cartelement-'.$this->faker()->numberBetween(1, 10)))
                // ->addOrderelement()
                // ->addSubscriptionReservation()
            ;

            $this->addReference('recipesubscription-'.$i, $recipesubscription);

            $manager->persist($recipesubscription);
            $recipesubscriptions[] = $recipesubscription;
        }

        // Create 20 Cart Element
        $cartelements = [];
        for ($i = 0; $i <= 20; ++$i) {
            $cartelement = (new CartElement())
                ->setQuantity(1 === mt_rand(0, 1) ? $this->faker()->numberBetween(10, 250) : null)
                ->setSubscriptionFee(1 === mt_rand(0, 1) ? $this->faker()->numberBetween(10, 50) : null)
                // ->setReservedSeats()
                ->setUser($this->getReference('user-'.$this->faker()->numberBetween(1, 10)))
                ->setRecipeSubscription($this->faker()->randomElement($recipesubscriptions))
            ;

            $this->addReference('cartelement-'.$i, $cartelement);

            $manager->persist($cartelement);
            $cartelements[] = $cartelement;
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
            AppCountryFixtures::class,
            AppCategoryFixtures::class,
            AppAmenityFixtures::class,
            AppAudienceFixtures::class,
            AppVenueTypeFixtures::class,
            //AppRolesFixtures::class
        ];
    }
}
