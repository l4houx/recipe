<?php

namespace App\Entity;

use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasIsActiveTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasReferenceTrait;
use App\Entity\Traits\HasRoles;
use App\Repository\RecipeDateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecipeDateRepository::class)]
class RecipeDate
{
    use HasIdTrait;
    use HasIsActiveTrait;
    use HasIsOnlineTrait;
    use HasReferenceTrait;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    #[Assert\Expression(
        'true === this.getIsOnline() or this.getHasSeatingPlan() !== null',
        message: 'The value should not be blank.',
        groups: ['create', 'update']
    )]
    private ?bool $hasSeatingPlan = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    private ?\DateTimeInterface $startdate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\LessThan(propertyPath: 'startdate', groups: ['create', 'update'])]
    private ?\DateTimeInterface $enddate = null;

    #[ORM\ManyToOne(inversedBy: 'recipedates')]
    private ?Recipe $recipe = null;

    #[ORM\ManyToOne(inversedBy: 'recipedates')]
    #[Assert\Expression(
        'true === this.getIsOnline() or true === this.getHasSeatingPlan() or this.getVenue() !== null',
        message: 'The value should not be blank.',
        groups: ['create', 'update']
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?Venue $venue = null;

    /**
     * @var collection<int, RecipeSubscription>
     */
    #[ORM\OneToMany(mappedBy: 'recipedate', targetEntity: RecipeSubscription::class, orphanRemoval: true, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Assert\Valid(groups: ['create', 'update'])]
    private Collection $subscriptions;

    #[ORM\ManyToOne(inversedBy: 'recipeDates')]
    #[Assert\Expression(
        'true !== this.getHasSeatingPlan() or this.getSeatingPlan() !== null',
        message: 'The value should not be blank.',
        groups: ['create', 'update']
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?VenueSeatingPlan $seatingPlan = null;

    #[ORM\ManyToMany(targetEntity: Scanner::class, inversedBy: 'recipedates', cascade: ['persist', 'merge'])]
    #[ORM\JoinTable(name: 'recipedate_pointofsale')]
    #[ORM\JoinColumn(name: 'recipedate_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'pointofsale_id', referencedColumnName: 'id')]
    private Collection $scanners;

    #[ORM\ManyToMany(targetEntity: PointOfSale::class, inversedBy: 'recipedates', cascade: ['persist', 'merge'])]
    #[ORM\JoinTable(name: 'recipedate_scanner')]
    #[ORM\JoinColumn(name: 'recipedate_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'scanner_id', referencedColumnName: 'id')]
    private Collection $pointofsales;

    /**
     * @var collection<int, PayoutRequest>
     */
    #[ORM\OneToMany(mappedBy: 'recipeDate', targetEntity: PayoutRequest::class, cascade: ['persist', 'remove'], fetch: 'LAZY')]
    private Collection $payoutRequests;

    public function __construct()
    {
        $this->reference = $this->generateReference(10);
        $this->subscriptions = new ArrayCollection();
        $this->scanners = new ArrayCollection();
        $this->pointofsales = new ArrayCollection();
        $this->payoutRequests = new ArrayCollection();
    }

    public function getSubscriptionBySectionName($sectionName): ?RecipeSubscription
    {
        foreach ($this->subscriptions as $subscription) {
            // foreach ($this->getSeatingPlan()->getDesign()['sections'] as $section) {
            foreach ($subscription->getSeatingPlanSections() as $section) {
                if ($section == $sectionName) {
                    return $subscription;
                }
            }
        }

        return null;
    }

    public function getSubscriptionIdBySectionName($sectionName)
    {
        foreach ($this->subscriptions as $subscription) {
            // foreach ($this->getSeatingPlan()->getDesign()['sections'] as $section) {
            foreach ($subscription->getSeatingPlanSections() as $section) {
                if ($section == $sectionName) {
                    return $subscription->getId();
                }
            }
        }

        return null;
    }

    public function payoutRequested(): bool {
        foreach ($this->payoutRequests as $payoutRequest) {
            if ($payoutRequest->getStatus() == 0 || $payoutRequest->getStatus() == 1) {
                return true;
            }
        }
        return false;
    }

    public function payoutRequestStatusClass() {
        foreach ($this->payoutRequests as $payoutRequest) {
            if ($payoutRequest->getStatus() == 0 || $payoutRequest->getStatus() == 1) {
                return $payoutRequest->getStatusClass();
            }
        }
        return "Unknown";
    }

    public function payoutRequestStatus() {
        foreach ($this->payoutRequests as $payoutRequest) {
            if ($payoutRequest->getStatus() == 0 || $payoutRequest->getStatus() == 1) {
                return $payoutRequest->stringifyStatus();
            }
        }
        return "Unknown";
    }

    public function canBeScannedBy($scanner)
    {
        return $this->getScanners()->contains($scanner);
    }

    public function isOnSaleByPos($pointOfSale)
    {
        return $this->getPointofsales()->contains($pointOfSale);
    }

    public function getTotalCheckInPercentage()
    {
        if (0 == $this->getOrderElementsQuantitySum()) {
            return 0;
        }

        return round(($this->getScannedSubscriptionsCount() / $this->getOrderElementsQuantitySum()) * 100);
    }

    public function getScannedSubscriptionsCount()
    {
        $count = 0;
        foreach ($this->subscriptions as $subscription) {
            $count += $subscription->getScannedSubscriptionsCount();
        }

        return $count;
    }

    public function getTotalSalesPercentage()
    {
        if (0 == $this->getSubscriptionsQuantitySum()) {
            return 0;
        } else {
            return round(($this->getOrderElementsQuantitySum() / $this->getSubscriptionsQuantitySum()) * 100);
        }
    }

    public function getSubscriptionsQuantitySum()
    {
        $sum = 0;
        foreach ($this->subscriptions as $recipeDateSubscription) {
            $sum += $recipeDateSubscription->getQuantity();
        }

        return $sum;
    }

    public function getSales(string $role = 'all', string $user = 'all', bool $formattedForPayoutApproval = false, bool $includeFees = false)
    {
        $sum = 0;
        foreach ($this->subscriptions as $recipeDateSubscription) {
            $sum += $recipeDateSubscription->getSales($role, $user, $formattedForPayoutApproval, $includeFees);
        }

        return $sum;
    }

    public function getTotalSubscriptionFees()
    {
        return $this->getSales('all', 'all', false, true) - $this->getSales();
    }

    public function getSubscriptionPricePercentageCutSum(string $role = 'all')
    {
        $sum = 0;
        foreach ($this->subscriptions as $recipeDateSubscription) {
            $sum += $recipeDateSubscription->getSubscriptionPricePercentageCutSum($role);
        }

        return $sum;
    }

    public function getRestaurantPayoutAmount()
    {
        return $this->getSales() - $this->getSubscriptionPricePercentageCutSum() - $this->getSales(HasRoles::POINTOFSALE);
    }

    public function displayPosNames(): string
    {
        $pointofsales = '';
        if (count($this->pointofsales) > 0) {
            foreach ($this->pointofsales as $pointofsale) {
                $pointofsales .= $pointofsale->getName().', ';
            }
        }

        return rtrim($pointofsales, ', ');
    }

    public function displayScannersNames(): string
    {
        $scanners = '';
        if (count($this->scanners) > 0) {
            foreach ($this->scanners as $scanner) {
                $scanners .= $scanner->getName().', ';
            }
        }

        return rtrim($scanners, ', ');
    }

    public function getOrderElementsQuantitySum(int $status = 1, string $user = 'all', string $role = 'all')
    {
        $sum = 0;
        foreach ($this->subscriptions as $subscription) {
            $sum += $subscription->getOrderElementsQuantitySum($status, $user, $role);
        }

        return $sum;
    }

    public function isSoldOut(): bool
    {
        foreach ($this->subscriptions as $subscription) {
            if (!$subscription->isSoldOut()) {
                return false;
            }
        }

        return true;
    }

    public function hasASubscriptionOnSale(): bool
    {
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->isOnSale()) {
                return true;
            }
        }

        return false;
    }

    public function isOnSale(): bool
    {
        return
                $this->recipe->getRestaurant()->getUser()->isVerified() && $this->isActive && $this->recipe->getIsOnline() && ($this->getStartdate() > new \DateTime()) && (!$this->isSoldOut()) && $this->hasASubscriptionOnSale() && (!$this->payoutRequested())
        ;
    }

    public function stringifyStatus(): string
    {
        if (!$this->recipe->getRestaurant()->getUser()->isVerified()) {
            return 'Restaurant is disabled';
        } elseif (!$this->recipe->getIsOnline()) {
            return 'Recipe is not published';
        } elseif (!$this->isActive) {
            return 'Recipe date is disabled';
        } elseif ($this->getStartdate() < new \DateTime()) {
            return 'Recipe already started';
        } elseif ($this->isSoldOut()) {
            return 'Sold out';
        } elseif ($this->payoutRequested()) {
            return 'Locked (Payout request '.strtolower($this->payoutRequestStatus()).')';
        } elseif (!$this->hasASubscriptionOnSale()) {
            return 'No subscription on sale';
        } else {
            return 'On sale';
        }
    }

    public function stringifyStatusClass(): string
    {
        if (!$this->recipe->getRestaurant()->getUser()->isVerified()) {
            return 'danger';
        } elseif (!$this->isActive) {
            return 'danger';
        } elseif (!$this->recipe->getIsOnline()) {
            return 'warning';
        } elseif ($this->getStartdate() < new \DateTime()) {
            return 'info';
        } elseif ($this->isSoldOut()) {
            return 'warning';
        } elseif ($this->payoutRequested()) {
            return 'warning';
        } elseif (!$this->hasASubscriptionOnSale()) {
            return 'danger';
        } else {
            return 'success';
        }
    }

    public function isFree()
    {
        foreach ($this->subscriptions as $subscription) {
            if (!$subscription->getIsFree()) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function getCheapestSubscription(): RecipeSubscription
    {
        $cheapestsubscription = $this->subscriptions[0];
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getSalePrice() > 0) {
                if ($subscription->getSalePrice() < $cheapestsubscription->getSalePrice()) {
                    $cheapestsubscription = $subscription;
                }
            }
        }

        return $cheapestsubscription;
    }

    public function isHasSeatingPlan(): ?bool
    {
        return $this->hasSeatingPlan;
    }

    public function getHasSeatingPlan(): ?bool {
        return $this->hasSeatingPlan;
    }

    public function setHasSeatingPlan(?bool $hasSeatingPlan): static
    {
        $this->hasSeatingPlan = $hasSeatingPlan;

        return $this;
    }

    public function getStartdate(): ?\DateTimeInterface
    {
        return $this->startdate;
    }

    public function setStartdate(?\DateTimeInterface $startdate): static
    {
        $this->startdate = $startdate;

        return $this;
    }

    public function getEnddate(): ?\DateTimeInterface
    {
        return $this->enddate;
    }

    public function setEnddate(?\DateTimeInterface $enddate): static
    {
        $this->enddate = $enddate;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): static
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * @return Collection<int, RecipeSubscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(RecipeSubscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setRecipeDate($this);
        }

        return $this;
    }

    public function removeSubscription(RecipeSubscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getRecipeDate() === $this) {
                $subscription->setRecipeDate(null);
            }
        }

        return $this;
    }

    public function getSeatingPlan(): ?VenueSeatingPlan
    {
        return $this->seatingPlan;
    }

    public function setSeatingPlan(?VenueSeatingPlan $seatingPlan): static
    {
        $this->seatingPlan = $seatingPlan;

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
        }

        return $this;
    }

    public function removeScanner(Scanner $scanner): static
    {
        $this->scanners->removeElement($scanner);

        return $this;
    }

    /**
     * @return Collection<int, PointOfSale>
     */
    public function getPointofsales(): Collection
    {
        return $this->pointofsales;
    }

    public function addPointofsale(PointOfSale $pointofsale): static
    {
        if (!$this->pointofsales->contains($pointofsale)) {
            $this->pointofsales->add($pointofsale);
        }

        return $this;
    }

    public function removePointofsale(PointOfSale $pointofsale): static
    {
        $this->pointofsales->removeElement($pointofsale);

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
            $payoutRequest->setRecipeDate($this);
        }

        return $this;
    }

    public function removePayoutRequest(PayoutRequest $payoutRequest): static
    {
        if ($this->payoutRequests->removeElement($payoutRequest)) {
            // set the owning side to null (unless already changed)
            if ($payoutRequest->getRecipeDate() === $this) {
                $payoutRequest->setRecipeDate(null);
            }
        }

        return $this;
    }
}
