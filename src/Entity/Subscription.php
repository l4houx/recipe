<?php

namespace App\Entity;

use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasStripeEntityTrait;
use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    use HasIdTrait;
    use HasStripeEntityTrait;
    final public const ACTIVE = 1;
    final public const INACTIVE = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $state = self::INACTIVE;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Pricing $pricing;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $nextPayment;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $createdAt;

    public function isActive(): bool
    {
        return self::ACTIVE === $this->getState();
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPricing(): Pricing
    {
        return $this->pricing;
    }

    public function setPricing(Pricing $pricing): static
    {
        $this->pricing = $pricing;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getNextPayment(): \DateTimeInterface
    {
        return $this->nextPayment;
    }

    public function setNextPayment(\DateTimeInterface $nextPayment): static
    {
        $this->nextPayment = $nextPayment;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
