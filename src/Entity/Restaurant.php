<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Entity\Traits\HasLogoAndCoverVichTrait;
use App\Entity\Traits\HasSocialNetworksTrait;
use App\Entity\Traits\HasViewsTrait;
use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
#[Vich\Uploadable]
class Restaurant
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasLogoAndCoverVichTrait;
    use HasSocialNetworksTrait;
    use HasViewsTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000)]
    private ?string $content = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isShowvenuesmap = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isShowfollowers = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isShowreviews = true;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $allowTapToCheckInOnScannerApp = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $showRecipeDateStatsOnScannerApp = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * @var Collection<int, Scanner>
     */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: Scanner::class, cascade: ['remove'])]
    private Collection $scanners;

    /**
     * @var Collection<int, PointOfSale>
     */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: PointOfSale::class, cascade: ['remove'])]
    private Collection $pointofsales;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: Recipe::class, cascade: ['remove'])]
    private Collection $recipes;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'restaurants', cascade: ['persist', 'merge', 'remove'])]
    #[ORM\JoinTable(name: 'restaurant_category')]
    #[ORM\JoinColumn(name: 'restaurant_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id')]
    private Collection $categories;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'following', cascade: ['persist', 'merge'])]
    #[ORM\JoinTable(name: 'following')]
    #[ORM\JoinColumn(name: 'restaurant_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private Collection $followedby;

    #[ORM\ManyToOne(inversedBy: 'restaurants')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    /**
     * @var Collection<int, Venue>
     */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: Venue::class, cascade: ['remove'])]
    private Collection $venues;

    /**
     * @var Collection<int, PayoutRequest>
     */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: PayoutRequest::class, cascade: ['remove'])]
    private Collection $payoutRequests;

    /**
     * @var Collection<int, PaymentGateway>
     */
    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: PaymentGateway::class, cascade: ['remove'])]
    private Collection $paymentGateways;

    public function __construct()
    {
        $this->isShowvenuesmap = true;
        $this->isShowfollowers = true;
        $this->isShowreviews = true;
        $this->allowTapToCheckInOnScannerApp = true;
        $this->showRecipeDateStatsOnScannerApp = true;

        $this->scanners = new ArrayCollection();
        $this->pointofsales = new ArrayCollection();
        $this->recipes = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->followedby = new ArrayCollection();
        $this->venues = new ArrayCollection();
        $this->payoutRequests = new ArrayCollection();
        $this->paymentGateways = new ArrayCollection();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function hasSocialMedia(): bool
    {
        return $this->website || $this->email || $this->phone || $this->twitterurl || $this->instagramurl || $this->facebookurl || $this->googleplusurl || $this->linkedinurl;
    }

    public function isShowvenuesmap(): bool
    {
        return $this->isShowvenuesmap;
    }

    public function setIsShowvenuesmap(bool $isShowvenuesmap): static
    {
        $this->isShowvenuesmap = $isShowvenuesmap;

        return $this;
    }

    public function isShowfollowers(): bool
    {
        return $this->isShowfollowers;
    }

    public function setIsShowfollowers(bool $isShowfollowers): static
    {
        $this->isShowfollowers = $isShowfollowers;

        return $this;
    }

    public function isShowreviews(): bool
    {
        return $this->isShowreviews;
    }

    public function setIsShowreviews(bool $isShowreviews): static
    {
        $this->isShowreviews = $isShowreviews;

        return $this;
    }

    public function isAllowTapToCheckInOnScannerApp(): ?bool
    {
        return $this->allowTapToCheckInOnScannerApp;
    }

    public function getAllowTapToCheckInOnScannerApp(): ?bool
    {
        return $this->allowTapToCheckInOnScannerApp;
    }

    public function setAllowTapToCheckInOnScannerApp(?bool $allowTapToCheckInOnScannerApp): static
    {
        $this->allowTapToCheckInOnScannerApp = $allowTapToCheckInOnScannerApp;

        return $this;
    }

    public function isShowRecipeDateStatsOnScannerApp(): ?bool
    {
        return $this->showRecipeDateStatsOnScannerApp;
    }

    public function getShowRecipeDateStatsOnScannerApp(): ?bool
    {
        return $this->showRecipeDateStatsOnScannerApp;
    }

    public function setShowRecipeDateStatsOnScannerApp(?bool $showRecipeDateStatsOnScannerApp): static
    {
        $this->showRecipeDateStatsOnScannerApp = $showRecipeDateStatsOnScannerApp;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Scanner>
     */
    public function getScanners(): Collection
    {
        return $this->scanners;
    }

    public function addScanner(Scanner $scanner): static
    {
        if (!$this->scanners->contains($scanner)) {
            $this->scanners->add($scanner);
            $scanner->setRestaurant($this);
        }

        return $this;
    }

    public function removeScanner(Scanner $scanner): static
    {
        if ($this->scanners->removeElement($scanner)) {
            // set the owning side to null (unless already changed)
            if ($scanner->getRestaurant() === $this) {
                $scanner->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PointOfSale>
     */
    public function getPointOfSales(): Collection
    {
        return $this->pointofsales;
    }

    public function addPointOfSale(PointOfSale $pointofsale): static
    {
        if (!$this->pointofsales->contains($pointofsale)) {
            $this->pointofsales->add($pointofsale);
            $pointofsale->setRestaurant($this);
        }

        return $this;
    }

    public function removePointOfSale(PointOfSale $pointofsale): static
    {
        if ($this->pointofsales->removeElement($pointofsale)) {
            // set the owning side to null (unless already changed)
            if ($pointofsale->getRestaurant() === $this) {
                $pointofsale->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): static
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->setRestaurant($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getRestaurant() === $this) {
                $recipe->setRestaurant(null);
            }
        }

        return $this;
    }

    public function displayCategories(): string
    {
        $categories = '';
        foreach ($this->categories as $category) {
            $categories .= $category->getName().', ';
        }

        return rtrim($categories, ', ');
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFollowedby(): Collection
    {
        return $this->followedby;
    }

    public function addFollowedby(User $followedby): static
    {
        if (!$this->followedby->contains($followedby)) {
            $this->followedby->add($followedby);
        }

        return $this;
    }

    public function removeFollowedby(User $followedby): static
    {
        if ($this->followedby->contains($followedby)) {
            $this->followedby->removeElement($followedby);
        }

        return $this;
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followedby->contains($user);
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Venue>
     */
    public function getVenues(): Collection
    {
        return $this->venues;
    }

    public function addVenue(Venue $venue): static
    {
        if (!$this->venues->contains($venue)) {
            $this->venues->add($venue);
            $venue->setRestaurant($this);
        }

        return $this;
    }

    public function removeVenue(Venue $venue): static
    {
        if ($this->venues->removeElement($venue)) {
            // set the owning side to null (unless already changed)
            if ($venue->getRestaurant() === $this) {
                $venue->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PayoutRequest>
     */
    public function getPayoutRequests(): Collection
    {
        return $this->payoutRequests;
    }

    public function addPayoutRequest(PayoutRequest $payoutRequest): static
    {
        if (!$this->payoutRequests->contains($payoutRequest)) {
            $this->payoutRequests->add($payoutRequest);
            $payoutRequest->setRestaurant($this);
        }

        return $this;
    }

    public function removePayoutRequest(PayoutRequest $payoutRequest): static
    {
        if ($this->payoutRequests->removeElement($payoutRequest)) {
            // set the owning side to null (unless already changed)
            if ($payoutRequest->getRestaurant() === $this) {
                $payoutRequest->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaymentGateway>
     */
    public function getPaymentGateways(): Collection
    {
        return $this->paymentGateways;
    }

    public function addPaymentGateway(PaymentGateway $paymentGateway): static
    {
        if (!$this->paymentGateways->contains($paymentGateway)) {
            $this->paymentGateways->add($paymentGateway);
            $paymentGateway->setRestaurant($this);
        }

        return $this;
    }

    public function removePaymentGateway(PaymentGateway $paymentGateway): static
    {
        if ($this->paymentGateways->removeElement($paymentGateway)) {
            // set the owning side to null (unless already changed)
            if ($paymentGateway->getRestaurant() === $this) {
                $paymentGateway->setRestaurant(null);
            }
        }

        return $this;
    }
}
