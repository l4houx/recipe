<?php

namespace App\Entity;

use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasIsActiveTrait;
use App\Entity\Traits\HasReferenceTrait;
use App\Repository\RecipeSubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecipeSubscriptionRepository::class)]
class RecipeSubscription
{
    use HasIdTrait;
    use HasIsActiveTrait;
    use HasReferenceTrait;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    #[Assert\Length(min: 2, max: 50, groups: ['create', 'update'])]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Assert\NotNull(groups: ['create', 'update'])]
    private bool $isFree = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\LessThan(propertyPath: 'price', groups: ['create', 'update'])]
    private ?string $promotionalPrice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\LessThan(propertyPath: 'quantity', groups: ['create', 'update'])]
    private ?int $subscriptionsperattendee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $salesstartdate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\LessThan(propertyPath: 'salesstartdate', groups: ['create', 'update'])]
    private ?\DateTimeInterface $salesenddate = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $position;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $reservedSeat = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $seatingPlanSections = null;

    /**
     * @var collection<int, CartElement>
     */
    #[ORM\OneToMany(mappedBy: 'recipesubscription', targetEntity: CartElement::class, cascade: ['remove'])]
    private Collection $cartelements;

    /**
     * @var collection<int, OrderElement>
     */
    #[ORM\OneToMany(mappedBy: 'recipesubscription', targetEntity: OrderElement::class, cascade: ['remove'])]
    private Collection $orderelements;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    private ?RecipeDate $recipedate = null;

    /**
     * @var collection<int, SubscriptionReservation>
     */
    #[ORM\OneToMany(mappedBy: 'recipesubscription', targetEntity: SubscriptionReservation::class, cascade: ['remove'])]
    private Collection $subscriptionReservations;

    public function __construct()
    {
        $this->reference = $this->generateReference(10);
        $this->cartelements = new ArrayCollection();
        $this->orderelements = new ArrayCollection();
        $this->subscriptionReservations = new ArrayCollection();
    }

    public function checkIfSeatIsInSubscriptionReservation($section, $row, $seat): bool
    {
        foreach ($this->subscriptionReservations as $subscriptionReservation) {
            if (!$subscriptionReservation->isExpired()) {
                foreach ($subscriptionReservation->getOrderelement()->getReservedSeats() as $reservedSeat) {
                    if ($reservedSeat['sectionId'] == $section['randomId'] && $reservedSeat['rowId'] == $row['randomId'] && $reservedSeat['seatNumber'] == $seat['number']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function isSeatAlreadyReservedByIds($sectionId, $rowId, $seatNumber): bool
    {
        foreach ($this->orderelements as $orderElement) {
            if (1 == $orderElement->getOrder()->getStatus()) {
                foreach ($orderElement->getSubscriptions() as $boughtSubscription) {
                    if ($boughtSubscription->getReservedSeat()['sectionId'] == $sectionId && $boughtSubscription->getReservedSeat()['rowId'] == $rowId && $boughtSubscription->getReservedSeat()['seatNumber'] == $seatNumber) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function isSeatAlreadyReserved($section, $row, $seatNumber): bool
    {
        foreach ($this->orderelements as $orderElement) {
            if (1 == $orderElement->getOrder()->getStatus()) {
                foreach ($orderElement->getSubscriptions() as $boughtSubscription) {
                    if ($boughtSubscription->getReservedSeat()['sectionId'] == $section['randomId'] && $boughtSubscription->getReservedSeat()['rowId'] == $row['randomId'] && $boughtSubscription->getReservedSeat()['seatNumber'] == $seatNumber['number']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getRecipeSubscriptionCartElementsByUserAndSection($user, $section)
    {
        $userCartElements = [];
        foreach ($this->cartelements as $cartElement) {
            if ($cartElement->getUser() == $user) {
                foreach ($cartElement->getReservedSeats() as $reservedSeat) {
                    if ($reservedSeat['sectionId'] == $section['randomId']) {
                        $userCartElements[] = $cartElement;
                    }
                }
            }
        }

        return $userCartElements;
    }

    public function isSeatInCart($user, $section, $row, $seatNumber): bool
    {
        foreach ($this->cartelements as $cartElement) {
            if ($cartElement->getUser() == $user) {
                foreach ($cartElement->getReservedSeats() as $reservedSeat) {
                    if ($reservedSeat['sectionId'] == $section['randomId'] && $reservedSeat['rowId'] == $row['randomId'] && $reservedSeat['seatNumber'] == $seatNumber['number']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function stringifySections(): string
    {
        return implode(', ', $this->seatingPlanSections);
    }

    public function getTotalCheckInPercentage()
    {
        if (0 == $this->getOrderElementsQuantitySum()) {
            return 0;
        }

        return round(($this->getScannedSubscriptionsCount() / $this->getOrderElementsQuantitySum()) * 100);
    }

    public function getSalesPercentage()
    {
        if (0 == $this->quantity) {
            return 0;
        } else {
            return round(($this->getOrderElementsQuantitySum() / $this->quantity) * 100);
        }
    }

    public function getScannedSubscriptionsCount(): int
    {
        $count = 0;
        foreach ($this->orderelements as $orderElement) {
            foreach ($orderElement->getSubscriptions() as $subscription) {
                if ($subscription->getIsScanned()) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    public function getSales(string $role = 'all', string $user = 'all', bool $formattedForPayoutApproval = false, bool $includeFees = false)
    {
        $sum = 0;
        foreach ($this->orderelements as $orderelement) {
            if (1 === $orderelement->getOrder()->getStatus() && ('all' == $role || $orderelement->getOrder()->getUser()->hasRole($role)) && ('all' == $user || $orderelement->getOrder()->getUser() == $user)) {
                $sum += $orderelement->getPrice($formattedForPayoutApproval);
            }
        }
        if ($includeFees) {
            $sum += $this->getTotalFees();
        }

        return $sum;
    }

    public function getSubscriptionPricePercentageCutSum(string $role = 'all')
    {
        $sum = 0;
        foreach ($this->orderelements as $orderelement) {
            if ('all' == $role || $orderelement->getOrder()->getUser()->hasRole($role)) {
                $sum += (($orderelement->getOrder()->getSubscriptionPricePercentageCut() * $orderelement->getUnitprice()) / 100) * $orderelement->getQuantity();
            }
        }

        return $sum;
    }

    public function getTotalSubscriptionFees(string $role = 'all', string $user = 'all')
    {
        $sum = 0;
        foreach ($this->orderelements as $orderelement) {
            if (1 === $orderelement->getOrder()->getStatus() && ('all' == $role || $orderelement->getOrder()->getUser()->hasRole($role)) && ('all' == $user || $orderelement->getOrder()->getUser() == $user) && !$this->isFree) {
                $sum += $orderelement->getOrder()->getSubscriptionFee() * $orderelement->getQuantity();
            }
        }

        return $sum;
    }

    public function getTotalFees()
    {
        $sum = 0;
        $sum += $this->getTotalSubscriptionFees();

        return $sum;
    }

    public function isOnSale(): bool
    {
        if (!$this->recipedate || !$this->recipedate->getRecipe() || !$this->recipedate->getRecipe()->getRestaurant() || !$this->recipedate->getRecipe()->getRestaurant()->getUser()) {
            return false;
        }

        return
                $this->recipedate->getRecipe()->getRestaurant()->getUser()->isVerified() && $this->recipedate->getRecipe()->getIsOnline() && $this->recipedate->getIsActive() && ($this->recipedate->getStartdate() >= new \DateTime()) && $this->isActive && !$this->isSoldOut() && ($this->salesstartdate < new \DateTime() || !$this->salesstartdate) && ($this->salesenddate > new \DateTime() || !$this->salesenddate) && (!$this->recipedate->payoutRequested())
        ;
    }

    public function stringifyStatus(): string
    {
        if (!$this->recipedate->getRecipe()->getRestaurant()->getUser()->isVerified()) {
            return 'Restaurant is disabled';
        } elseif (!$this->recipedate->getRecipe()->getIsOnline()) {
            return 'Recipe is not published';
        } elseif (!$this->recipedate->getIsActive()) {
            return 'Recipe date is disabled';
        } elseif ($this->recipedate->getStartdate() < new \DateTime()) {
            return 'Recipe already started';
        } elseif (!$this->isActive) {
            return 'Recipe subscription is disabled';
        } elseif ($this->isSoldOut()) {
            return 'Sold out';
        } elseif ($this->salesstartdate > new \DateTime() && $this->salesstartdate) {
            return "Sale didn't start yet";
        } elseif ($this->salesenddate < new \DateTime() && $this->salesstartdate) {
            return 'Sale ended';
        } elseif ($this->recipedate->payoutRequested()) {
            return 'Locked (Payout request '.strtolower($this->recipedate->payoutRequestStatus()).')';
        } else {
            return 'On sale';
        }
    }

    public function stringifyStatusClass(): string
    {
        if (!$this->recipedate->getRecipe()->getRestaurant()->getUser()->isVerified()) {
            return 'danger';
        } elseif (!$this->recipedate->getRecipe()->getIsOnline()) {
            return 'danger';
        } elseif (!$this->recipedate->getIsActive()) {
            return 'danger';
        } elseif ($this->recipedate->getStartdate() < new \DateTime()) {
            return 'info';
        } elseif (!$this->isActive) {
            return 'danger';
        } elseif ($this->isSoldOut()) {
            return 'warning';
        } elseif ($this->salesstartdate > new \DateTime() && $this->salesstartdate) {
            return 'info';
        } elseif ($this->salesenddate < new \DateTime() && $this->salesstartdate) {
            return 'warning';
        } elseif ($this->recipedate->payoutRequested()) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    public function getOrderElementsQuantitySum(int $status = 1, string $user = 'all', string $role = 'all')
    {
        $sum = 0;
        foreach ($this->orderelements as $orderelement) {
            if (('all' == $status || $orderelement->getOrder()->getStatus() === $status) && ('all' == $user || $orderelement->getOrder()->getUser() == $user) && ('all' == $role || $orderelement->getOrder()->getUser()->hasRole($role))) {
                $sum += $orderelement->getQuantity();
            }
        }

        return $sum;
    }

    public function getValidSubscriptionReservationsQuantitySum($user = null, $notUser = null)
    {
        $sum = 0;
        foreach ($this->subscriptionReservations as $subscriptionReservation) {
            if (!$subscriptionReservation->isExpired() && (null == $user || $subscriptionReservation->getUser() == $user) && (null == $notUser || $notUser !== $subscriptionReservation->getUser())) {
                $sum += $subscriptionReservation->getQuantity();
            }
        }

        return $sum;
    }

    public function getValidSubscriptionReservationExpirationDate($user)
    {
        foreach ($this->subscriptionReservations as $subscriptionReservation) {
            if (!$subscriptionReservation->isExpired() && $subscriptionReservation->getUser() == $user) {
                return $subscriptionReservation->getExpiresAt();
            }
        }

        return null;
    }

    public function getSubscriptionsLeftCount(bool $countValidSubscriptionReservations = true, $notUser = null)
    {
        if ($countValidSubscriptionReservations) {
            return $this->quantity - $this->getOrderElementsQuantitySum() - $this->getValidSubscriptionReservationsQuantitySum(null, $notUser);
        } else {
            return $this->quantity - $this->getOrderElementsQuantitySum();
        }
    }

    public function getMaxSubscriptionsForSaleCount($user)
    {
        $subscriptionsForSaleCount = 0;
        if ($this->subscriptionsperattendee) {
            if ($this->getSubscriptionsLeftCount() >= $this->subscriptionsperattendee) {
                $subscriptionsForSaleCount = $this->subscriptionsperattendee - $this->getOrderElementsQuantitySum(1, $user);
            } else {
                $subscriptionsForSaleCount = $this->getSubscriptionsLeftCount() - $this->subscriptionsperattendee - $this->getOrderElementsQuantitySum(1, $user);
            }
        } else {
            $subscriptionsForSaleCount = $this->quantity - $this->getOrderElementsQuantitySum();
        }
        if ($subscriptionsForSaleCount < 0) {
            $subscriptionsForSaleCount = 0;
        }

        return $subscriptionsForSaleCount;
    }

    public function isSoldOut(): bool
    {
        if (0 == $this->quantity || $this->getSubscriptionsLeftCount() > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function getSalePrice()
    {
        if ($this->promotionalPrice) {
            return (float) $this->getPromotionalPrice();
        } elseif ($this->price) {
            return (float) $this->getPrice();
        } else {
            return 0;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isFree(): ?bool
    {
        return $this->isFree;
    }

    public function getIsFree(): bool
    {
        return $this->isFree;
    }

    public function setIsFree(bool $isFree): static
    {
        $this->isFree = $isFree;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPromotionalPrice(): ?string
    {
        return $this->promotionalPrice;
    }

    public function setPromotionalPrice(?string $promotionalPrice): static
    {
        $this->promotionalPrice = $promotionalPrice;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSubscriptionsperattendee(): ?int
    {
        return $this->subscriptionsperattendee;
    }

    public function setSubscriptionsperattendee(?int $subscriptionsperattendee): static
    {
        $this->subscriptionsperattendee = $subscriptionsperattendee;

        return $this;
    }

    public function getSalesstartdate(): ?\DateTimeInterface
    {
        return $this->salesstartdate;
    }

    public function setSalesstartdate(?\DateTimeInterface $salesstartdate): static
    {
        $this->salesstartdate = $salesstartdate;

        return $this;
    }

    public function getSalesenddate(): ?\DateTimeInterface
    {
        return $this->salesenddate;
    }

    public function setSalesenddate(?\DateTimeInterface $salesenddate): static
    {
        $this->salesenddate = $salesenddate;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getReservedSeat(): ?array
    {
        return $this->reservedSeat;
    }

    public function setReservedSeat(?array $reservedSeat): static
    {
        $this->reservedSeat = $reservedSeat;

        return $this;
    }

    public function getSeatingPlanSections(): ?array
    {
        return $this->seatingPlanSections;
    }

    public function setSeatingPlanSections(?array $seatingPlanSections): static
    {
        $this->seatingPlanSections = $seatingPlanSections;

        return $this;
    }

    /**
     * @return Collection<int, CartElement>
     */
    public function getCartElements(): Collection
    {
        return $this->cartelements;
    }

    public function addCartElement(CartElement $cartelement): static
    {
        if (!$this->cartelements->contains($cartelement)) {
            $this->cartelements->add($cartelement);
            $cartelement->setRecipeSubscription($this);
        }

        return $this;
    }

    public function removeCartElement(CartElement $cartelement): static
    {
        if ($this->cartelements->removeElement($cartelement)) {
            // set the owning side to null (unless already changed)
            if ($cartelement->getRecipeSubscription() === $this) {
                $cartelement->setRecipeSubscription(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderElement>
     */
    public function getOrderelements(): Collection
    {
        return $this->orderelements;
    }

    public function addOrderelement(OrderElement $orderelement): static
    {
        if (!$this->orderelements->contains($orderelement)) {
            $this->orderelements->add($orderelement);
            $orderelement->setRecipeSubscription($this);
        }

        return $this;
    }

    public function removeOrderelement(OrderElement $orderelement): static
    {
        if ($this->orderelements->removeElement($orderelement)) {
            // set the owning side to null (unless already changed)
            if ($orderelement->getRecipeSubscription() === $this) {
                $orderelement->setRecipeSubscription(null);
            }
        }

        return $this;
    }

    public function getRecipeDate(): ?RecipeDate
    {
        return $this->recipedate;
    }

    public function setRecipeDate(?RecipeDate $recipedate): static
    {
        $this->recipedate = $recipedate;

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
            $subscriptionReservation->setRecipeSubscription($this);
        }

        return $this;
    }

    public function removeSubscriptionReservation(SubscriptionReservation $subscriptionReservation): static
    {
        if ($this->subscriptionReservations->removeElement($subscriptionReservation)) {
            // set the owning side to null (unless already changed)
            if ($subscriptionReservation->getRecipeSubscription() === $this) {
                $subscriptionReservation->setRecipeSubscription(null);
            }
        }

        return $this;
    }
}
