<?php

namespace App\Entity;

use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Repository\CartElementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartElementRepository::class)]
class CartElement
{
    use HasIdTrait;
    use HasGedmoTimestampTrait;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $subscriptionFee = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $reservedSeats = null;

    #[ORM\ManyToOne(inversedBy: 'cartelements')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'cartelements')]
    private ?RecipeSubscription $recipesubscription = null;

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): float
    {
        return (float) $this->recipesubscription->getSalePrice() * $this->quantity;
    }

    public function getSubscriptionFee(): ?string
    {
        return $this->subscriptionFee;
    }

    public function setSubscriptionFee(?string $subscriptionFee): static
    {
        $this->subscriptionFee = $subscriptionFee;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
}
