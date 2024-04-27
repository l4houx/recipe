<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
use App\Entity\Country;
use App\Entity\Keyword;
use App\Entity\PostCategory;
use App\Entity\Traits\HasRoles;
use App\DataFixtures\FakerTrait;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppPostFixtures extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SluggerInterface $slugger
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var array<Country> $countries */
        $countries = $manager->getRepository(Country::class)->findAll();

        // Create 1 User Admin
        $authors = [];

        // User Admin
        /** @var User $author */
        $author = (new User());
        $author
            ->setTeamName('author.jpg')
            ->setRoles([HasRoles::ADMIN])
            ->setLastname('Tom')
            ->setFirstname('Doe')
            ->setUsername('tom-admin')
            ->setSlug('tom-admin')
            ->setEmail('tom-admin@yourdomain.com')
            ->setPhone($this->faker()->phoneNumber())
            ->setIsTeam(true)
            ->setIsVerified(true)
            ->setAbout($this->faker()->realText(254))
            ->setDesignation('Admin Staff')
            ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setLastLoginIp($this->faker()->ipv4())
            ->setBirthdate(\DateTime::createFromInterface($this->faker()->dateTime()))
            ->setStreet($this->faker()->streetAddress())
            ->setStreet2($this->faker()->secondaryAddress())
            ->setCity($this->faker()->city())
            ->setState($this->faker()->region())
            ->setPostalcode($this->faker()->postcode())
            ->setCountry($this->faker()->randomElement($countries))
        ;

        $manager->persist(
            $author->setPassword(
                $this->hasher->hashPassword($author, 'author')
            )
        );
        $authors[] = $author;

        ///** @var array<array-key, PostCategory> $categories */
        //$categories = $manager->getRepository(PostCategory::class)->findBy(['isOnline' => true], ['createdAt' => 'DESC']);

        // Create 20 Posts
        $posts = [];
        for ($i = 0; $i <= 20; ++$i) {
            $post = new Post();
            $post
                ->setTitle($this->faker()->unique()->sentence())
                ->setSlug($this->slugger->slug($post->getTitle())->lower())
                ->setContent($this->faker()->paragraphs(10, true))
                ->setReadtime(mt_rand(0, 1) === 1 ? rand(10, 160) : null)
                ->setViews(rand(10, 160))
                ->setAuthor($author)
                ->setIsOnline($this->faker()->numberBetween(0, 1))
                ->setTags(mt_rand(0, 1) === 1 ? $this->faker()->unique()->word() : null)
            ;

            $category = $this->getReference('category-' . $this->faker()->numberBetween(1, 16));
            $post->setCategory($category);

            $manager->persist($post);
            $posts[] = $post;

            // Create Comments
            for ($k = 1; $k <= $this->faker()->numberBetween(1, 5); ++$k) {
                $comment = (new Comment())
                    ->setIp($this->faker()->ipv4)
                    ->setContent($this->faker()->paragraph())
                    ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                    ->setPost($post)
                    ->setParent(null)
                    ->setIsApproved($this->faker()->numberBetween(0, 1))
                    ->setIsRGPD(true)
                    ->setPublishedAt(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
                ;

                $manager->persist($comment);
            }
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
            AppPostCategoryFixtures::class
        ];
    }
}
