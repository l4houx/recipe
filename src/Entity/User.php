<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Setting\HomepageHeroSetting;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasNotifiableTrait;
use App\Entity\Traits\HasPremiumTrait;
use App\Entity\Traits\HasProfileDetailsTrait;
use App\Entity\Traits\HasRoles;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Vich\Uploadable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
#[UniqueEntity(fields: ['email'], message: 'This email address is already in use')]
#[UniqueEntity(fields: ['username'], message: 'This username is already in use')]
#[ApiResource(
    security: "is_granted('ROLE_APPLICATION_ADMIN')",
    operations: [
        new \ApiPlatform\Metadata\Get(openapi: false),
        new \ApiPlatform\Metadata\Post(openapi: false),
        new \ApiPlatform\Metadata\Put(openapi: false),
        new \ApiPlatform\Metadata\Patch(openapi: false),
        new \ApiPlatform\Metadata\Delete(openapi: false),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create', 'user:update']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Stringable
{
    use HasProfileDetailsTrait;
    use HasPremiumTrait;
    use HasNotifiableTrait;
    use HasDeletedAtTrait;

    public const USER_LIMIT = HasLimit::USER_LIMIT;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['user:read'])]
    private ?int $id = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private array $roles = [HasRoles::DEFAULT];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    /**
     * @var collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $posts;

    /**
     * @var collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $comments;

    /**
     * @var collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'author', orphanRemoval: true, cascade: ['remove'])]
    private Collection $reviews;

    /**
     * @var collection<int, Testimonial>
     */
    #[ORM\OneToMany(targetEntity: Testimonial::class, mappedBy: 'author', orphanRemoval: true, cascade: ['remove'])]
    private Collection $testimonials;

    /**
     * @var collection<int, Recipe>
     */
    #[ORM\ManyToMany(targetEntity: Recipe::class, mappedBy: 'addedtofavoritesby', fetch: 'LAZY', cascade: ['remove'])]
    private Collection $favorites;

    /**
     * @var Collection<int, Restaurant>
     */
    #[ORM\ManyToMany(targetEntity: Restaurant::class, mappedBy: 'followedby', fetch: 'LAZY', cascade: ['remove'])]
    private Collection $following;

    #[ORM\ManyToOne(inversedBy: 'restaurants')]
    private ?HomepageHeroSetting $isrestaurantonhomepageslider = null;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Application::class, orphanRemoval: true)]
    #[Groups(['user:read'])]
    private Collection $applications;

    #[ORM\OneToOne(targetEntity: Restaurant::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[Assert\Valid]
    private ?Restaurant $restaurant = null;

    #[ORM\OneToOne(targetEntity: PointOfSale::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?PointOfSale $pointofsale = null;

    #[ORM\OneToOne(targetEntity: Scanner::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Scanner $scanner = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class, cascade: ['remove'])]
    private Collection $orders;

    /**
     * @var Collection<int, CartElement>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CartElement::class, cascade: ['remove'])]
    private Collection $cartelements;

    /**
     * @var Collection<int, SubscriptionReservation>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: SubscriptionReservation::class, cascade: ['remove'])]
    private Collection $subscriptionReservations;

    public function hasBoughtASubscriptionForRecipe($recipe): Order|false
    {
        foreach ($this->orders as $order) {
            if (1 == $order->getStatus() && $order->containsRecipe($recipe)) {
                return $order;
            }
        }

        return false;
    }

    public function getOrdersQuantitySum(int $status = 1, string $upcomingsubscriptions = 'all')
    {
        $sum = 0;
        foreach ($this->orders as $order) {
            foreach ($order->getOrderelements() as $orderelement) {
                if ('all' === $status || $orderelement->getOrder()->getStatus() === $status && ('all' === $upcomingsubscriptions || (1 == $upcomingsubscriptions && $orderelement->getRecipeSubscription()->getRecipeDate()->getStartdate() >= new \DateTime()) || (0 == $upcomingsubscriptions && $orderelement->getRecipeSubscription()->getRecipeDate()->getStartdate() < new \DateTime()))) {
                    $sum += $orderelement->getquantity();
                }
            }
        }

        return $sum;
    }

    public function isRecipeSubscriptionInCart($recipesubscription)
    {
        foreach ($this->cartelements as $cartelement) {
            if ($cartelement->getRecipeSubscription() == $recipesubscription) {
                return $cartelement->getQuantity();
            }
        }

        return false;
    }

    public function getCartelementByRecipeSubscriptionReference($recipesubscriptionreference): ?CartElement
    {
        foreach ($this->cartelements as $cartelement) {
            if ($cartelement->getRecipeSubscription()->getReference() == $recipesubscriptionreference) {
                return $cartelement;
            }
        }

        return null;
    }

    public function getSubscriptionsInCartPriceSum(bool $includeFees = false): float
    {
        $sum = 0;
        foreach ($this->cartelements as $cartelement) {
            $sum += $cartelement->getPrice();
        }
        if ($includeFees) {
            $sum += $this->getTotalFees();
        }

        return (float) $sum;
    }

    public function getTotalSubscriptionFees(): int|float
    {
        if (!count($this->cartelements)) {
            return 0;
        }
        $sum = 0;
        foreach ($this->cartelements as $cartelement) {
            $sum += $cartelement->getQuantity() * $cartelement->getSubscriptionFee();
        }

        return (float) $sum;
    }

    public function getTotalFees()
    {
        $sum = 0;
        $sum += $this->getTotalSubscriptionFees();

        return $sum;
    }

    public function getNotFreeSubscriptionsInCartQuantitySum()
    {
        $sum = 0;
        foreach ($this->cartelements as $cartelement) {
            if (!$cartelement->getRecipeSubscription()->getIsFree()) {
                $sum += $cartelement->getQuantity();
            }
        }

        return $sum;
    }

    public function getSubscriptionsInCartQuantitySum()
    {
        $sum = 0;
        foreach ($this->cartelements as $cartelement) {
            $sum += $cartelement->getQuantity();
        }

        return $sum;
    }

    public function __toString(): string
    {
        return $this->username ?? $this->email;
    }

    public function __construct()
    {
        $this->isVerified = false;
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->testimonials = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->cartelements = new ArrayCollection();
        $this->subscriptionReservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(HasRoles::SUPERADMIN);
    }

    public function setSuperAdmin($boolean): static
    {
        if (true === $boolean) {
            $this->addRole(HasRoles::SUPERADMIN);
        } else {
            $this->removeRole(HasRoles::SUPERADMIN);
        }

        return $this;
    }

    public function removeRole(string $role): static
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function getRole(): string
    {
        if ($this->hasRole(HasRoles::CREATOR)) {
            return '<span class="badge me-2 bg-success-soft">Creator</span>';
        } elseif ($this->hasRole(HasRoles::RESTAURANT)) {
            return '<span class="badge me-2 bg-primary-soft">Restaurant</span>';
        } elseif ($this->hasRole(HasRoles::POINTOFSALE)) {
            return '<span class="badge me-2 bg-success-soft">Point of sale</span>';
        } elseif ($this->hasRole(HasRoles::SCANNER)) {
            return '<span class="badge me-2 bg-info-soft">Scanner</span>';
        } elseif ($this->hasRole(HasRoles::SUPERADMIN) || $this->hasRole(HasRoles::ADMINAPPLICATION)) {
            return '<span class="badge me-2 bg-danger-soft">Administrator</span>';
        } elseif ($this->hasRole(HasRoles::ADMIN)) {
            return '<span class="badge me-2 bg-light-danger-soft">Admin</span>';
        } elseif ($this->hasRole(HasRoles::MODERATOR)) {
            return '<span class="badge me-2 bg-light-secondary-soft">Moderator</span>';
        } else {
            return '<span class="badge me-2 bg-light-warning-soft">N/A</span>';
        }
    }

    public function getCrossRoleName(): string
    {
        if ($this->hasRole(HasRoles::CREATOR)) {
            return $this->getFullName();
        } elseif ($this->hasRole(HasRoles::RESTAURANT) && $this->restaurant) {
            return $this->restaurant->getName();
        } elseif ($this->hasRole(HasRoles::POINTOFSALE) && $this->pointofsale) {
            return $this->pointofsale->getName();
        } elseif ($this->hasRole(HasRoles::SCANNER) && $this->scanner) {
            return $this->scanner->getName();
        } elseif ($this->hasRole(HasRoles::ADMIN)) {
            return $this->getFullName();
        } elseif ($this->hasRole(HasRoles::MODERATOR)) {
            return $this->getFullName();
        } else {
            return 'N/A';
        }
    }

    public function addRole(string $role): static
    {
        $role = strtoupper($role);
        if (HasRoles::DEFAULT === $role) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = HasRoles::DEFAULT;

        if ($this->isVerified) {
            $roles[] = HasRoles::VERIFIED;
        }

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        // $this->roles = $roles;

        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->username,
            $this->firstname,
            $this->lastname,
            $this->email,
            $this->password,
        ];
    }

    public function __unserialize(array $data): void
    {
        if (count($data) === 6) {
            [
                $this->id,
                $this->username,
                $this->firstname,
                $this->lastname,
                $this->email,
                $this->password,
            ] = $data;
        }
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setAuthor($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getAuthor() === $this) {
                $review->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Testimonial>
     */
    public function getTestimonials(): Collection
    {
        return $this->testimonials;
    }

    public function addTestimonial(Testimonial $testimonial): static
    {
        if (!$this->testimonials->contains($testimonial)) {
            $this->testimonials->add($testimonial);
            $testimonial->setAuthor($this);
        }

        return $this;
    }

    public function removeTestimonial(Testimonial $testimonial): static
    {
        if ($this->testimonials->removeElement($testimonial)) {
            // set the owning side to null (unless already changed)
            if ($testimonial->getAuthor() === $this) {
                $testimonial->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Recipe $favorite): static
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->addAddedtofavoritesby($this);
        }

        return $this;
    }

    public function removeFavorite(Recipe $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            $favorite->removeAddedtofavoritesby($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Restaurant>
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(Restaurant $following): static
    {
        if (!$this->following->contains($following)) {
            $this->following->add($following);
            $following->addFollowedby($this);
        }

        return $this;
    }

    public function removeFollowing(Restaurant $following): static
    {
        if ($this->following->removeElement($following)) {
            $following->removeFollowedby($this);
        }

        return $this;
    }

    public function getIsrestaurantonhomepageslider(): ?HomepageHeroSetting
    {
        return $this->isrestaurantonhomepageslider;
    }

    public function setIsrestaurantonhomepageslider(?HomepageHeroSetting $isrestaurantonhomepageslider): static
    {
        $this->isrestaurantonhomepageslider = $isrestaurantonhomepageslider;

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setUser($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getUser() === $this) {
                $application->setUser(null);
            }
        }

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getPointOfSale(): ?PointOfSale
    {
        return $this->pointofsale;
    }

    public function setPointOfSale(?PointOfSale $pointofsale): static
    {
        $this->pointofsale = $pointofsale;

        return $this;
    }

    public function getScanner(): ?Scanner
    {
        return $this->scanner;
    }

    public function setScanner(?Scanner $scanner): static
    {
        $this->scanner = $scanner;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CartElement>
     */
    public function getCartelements(): Collection
    {
        return $this->cartelements;
    }

    public function addCartelement(CartElement $cartelement): static
    {
        if (!$this->cartelements->contains($cartelement)) {
            $this->cartelements->add($cartelement);
            $cartelement->setUser($this);
        }

        return $this;
    }

    public function removeCartelement(CartElement $cartelement): static
    {
        if ($this->cartelements->removeElement($cartelement)) {
            // set the owning side to null (unless already changed)
            if ($cartelement->getUser() === $this) {
                $cartelement->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SubscriptionReservation>
     */
    public function getSubscriptionReservations(): Collection
    {
        return $this->subscriptionReservations;
    }

    public function addSubscriptionReservation(SubscriptionReservation $subscriptionReservation): static
    {
        if (!$this->subscriptionReservations->contains($subscriptionReservation)) {
            $this->subscriptionReservations->add($subscriptionReservation);
            $subscriptionReservation->setUser($this);
        }

        return $this;
    }

    public function removeSubscriptionReservation(SubscriptionReservation $subscriptionReservation): static
    {
        if ($this->subscriptionReservations->removeElement($subscriptionReservation)) {
            // set the owning side to null (unless already changed)
            if ($subscriptionReservation->getUser() === $this) {
                $subscriptionReservation->setUser(null);
            }
        }

        return $this;
    }
}
