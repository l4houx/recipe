<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comment;
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
        // Create 1 User Admin
        $authors = [];

        // User Admin
        /** @var User $author */
        $author = (new User());
        $author
            ->setRoles([HasRoles::ADMIN])
            ->setAvatar($this->faker()->unique()->userName())
            ->setCountry('FR')
            ->setLastname('Tom')
            ->setFirstname('Doe')
            ->setUsername('tom-admin')
            ->setSlug('tom-admin')
            ->setEmail('tom-admin@yourdomain.com')
            //->setPhone($this->faker()->phoneNumber())
            ->setIsTeam(true)
            ->setIsVerified(true)
            ->setAbout($this->faker()->realText(254))
            ->setDesignation('Admin Staff')
            ->setLastLogin(\DateTimeImmutable::createFromMutable($this->faker()->dateTime()))
            ->setLastLoginIp($this->faker()->ipv4())
        ;

        $manager->persist(
            $author->setPassword(
                $this->hasher->hashPassword($author, 'author')
            )
        );
        $authors[] = $author;

        /** @var array<array-key, PostCategory> $categories */
        $categories = $manager->getRepository(PostCategory::class)->findAll();

        /** @var array<array-key, Keyword> $keywords */
        $keywords = $manager->getRepository(Keyword::class)->findAll();

        // Create 10 Posts
        $posts = [];
        for ($i = 0; $i <= 10; ++$i) {
            shuffle($categories);
            shuffle($keywords);
            $post = new Post();
            $post
                ->setTitle($this->faker()->unique()->sentence())
                ->setSlug($this->slugger->slug($post->getTitle())->lower())
                ->setContent($this->faker()->paragraphs(10, true))
                ->setReadtime(rand(10, 160))
                ->setViews(rand(10, 160))
                //->setAuthor($this->faker()->boolean(50) ? $customer : $admin)
                ->setAuthor($author)
                ->setIsOnline($this->faker()->numberBetween(0, 1))
            ;

            foreach (array_slice($categories, 0, 1) as $category) {
                $post->getPostcategories()->add($category);
            }

            foreach (array_slice($keywords, 0, 2) as $keyword) {
                $post->getKeywords()->add($keyword);
            }

            $manager->persist($post);

            $posts[] = $post;

            // Create Comments
            for ($k = 1; $k <= $this->faker()->numberBetween(1, 5); ++$k) {
                $comment = new Comment();
                $comment
                    //->setAuthor($this->faker()->boolean(50) ? $customer : $admin)
                    ->setAuthor($this->getReference('user-' . $this->faker()->numberBetween(1, 10)))
                    ->setContent($this->faker()->paragraph())
                    ->setIsApproved($this->faker()->numberBetween(0, 1))
                    //->setIsReply()
                    //->setIsRGPD(true)
                    ->setIp($this->faker()->ipv4)
                    ->setPost($post)
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
            AppPostCategoryFixtures::class,
            AppKeywordFixtures::class
        ];
    }
}
