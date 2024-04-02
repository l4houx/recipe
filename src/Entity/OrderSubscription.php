<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Repository\OrderSubscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderSubscriptionRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class OrderSubscription
{
    use HasIdTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Assert\NotNull(groups: ['create', 'update'])]
    private bool $isScanned = false;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $reference;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $reservedSeat = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    private ?OrderElement $orderelement = null;

    public function isScanned(): bool
    {
        return $this->isScanned;
    }

    public function getIsScanned(): bool
    {
        return $this->isScanned;
    }

    public function setIsScanned(bool $isScanned): static
    {
        $this->isScanned = $isScanned;

        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    public function getOrderelement(): ?OrderElement
    {
        return $this->orderelement;
    }

    public function setOrderelement(?OrderElement $orderelement): static
    {
        $this->orderelement = $orderelement;

        return $this;
    }
}
