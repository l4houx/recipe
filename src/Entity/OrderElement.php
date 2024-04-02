<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasIdTrait;
use App\Repository\OrderElementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: OrderElementRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class OrderElement
{
    use HasIdTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $unitprice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $reservedSeats = null;

    #[ORM\ManyToOne(inversedBy: 'orderelements')]
    private ?RecipeSubscription $recipesubscription = null;

    #[ORM\ManyToOne(inversedBy: 'orderelements')]
    private ?Order $order = null;

    /**
     * @var collection<int, OrderSubscription>
     */
    #[ORM\OneToMany(mappedBy: 'orderelement', targetEntity: OrderSubscription::class, cascade: ['persist', 'remove'])]
    private Collection $subscriptions;

    /**
     * @var collection<int, SubscriptionReservation>
     */
    #[ORM\OneToMany(mappedBy: 'orderelement', targetEntity: SubscriptionReservation::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private Collection $subscriptionReservations;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
        $this->subscriptionReservations = new ArrayCollection();
    }

    public function getScannedSubscriptionsCount(): int
    {
        $count = 0;
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->getIsScanned()) {
                ++$count;
            }
        }

        return $count;
    }

    public function belongsToRestaurant(string $slug): bool
    {
        if ($this->recipesubscription->getRecipeDate()->getRecipe()->getRestaurant()->getSlug() == $slug) {
            return true;
        } else {
            return false;
        }
    }

    public function displayUnitPrice(): float
    {
        return (float) $this->unitprice;
    }

    public function getPrice(bool $formattedForPayoutApproval = false)
    {
        if ($formattedForPayoutApproval) {
            return $this->unitprice * $this->quantity;
        }

        return (float) $this->unitprice * $this->quantity;
    }

    public function getUnitprice(): ?string
    {
        return $this->unitprice;
    }

    public function setUnitprice(?string $unitprice): static
    {
        $this->unitprice = $unitprice;

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

    public function getReservedSeats(): ?array
    {
        return $this->reservedSeats;
    }

    public function setReservedSeats(?array $reservedSeats): static
    {
        $this->reservedSeats = $reservedSeats;

        return $this;
    }

    public function getRecipeSubscription(): ?RecipeSubscription
    {
        return $this->recipesubscription;
    }

    public function setRecipeSubscription(?RecipeSubscription $recipesubscription): static
    {
        $this->recipesubscription = $recipesubscription;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Collection<int, OrderSubscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(OrderSubscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setOrderelement($this);
        }

        return $this;
    }

    public function removeSubscription(OrderSubscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getOrderelement() === $this) {
                $subscription->setOrderelement(null);
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
            $subscriptionReservation->setOrderelement($this);
        }

        return $this;
    }

    public function removeSubscriptionReservation(SubscriptionReservation $subscriptionReservation): static
    {
        if ($this->subscriptionReservations->removeElement($subscriptionReservation)) {
            // set the owning side to null (unless already changed)
            if ($subscriptionReservation->getOrderelement() === $this) {
                $subscriptionReservation->setOrderelement(null);
            }
        }

        return $this;
    }
}
