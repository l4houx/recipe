<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasExpiredAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\SubscriptionReservationRepository;

#[ORM\Entity(repositoryClass: SubscriptionReservationRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class SubscriptionReservation
{
    use HasIdTrait;
    use HasExpiredAtTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptionReservations')]
    private ?OrderElement $orderelement = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptionReservations')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptionReservations')]
    private ?RecipeSubscription $recipesubscription = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrderelement(): ?OrderElement
    {
        return $this->orderelement;
    }

    public function setOrderelement(?OrderElement $orderelement): static
    {
        $this->orderelement = $orderelement;

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
