<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasPremiumTrait
{
    use HasStripeEntityTrait;

    #[ORM\Column(type: Types::STRING, options: ['default' => null] , nullable: true)]
    private ?string $invoiceInfo = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $premiumEnd = null;

    public function isPremium(): bool
    {
        return $this->premiumEnd > new \DateTime();
    }

    public function getPremiumEnd(): ?\DateTimeImmutable
    {
        return $this->premiumEnd;
    }

    public function setPremiumEnd(?\DateTimeImmutable $premiumEnd): static
    {
        $this->premiumEnd = $premiumEnd;

        return $this;
    }

    public function getInvoiceInfo(): ?string
    {
        return $this->invoiceInfo;
    }

    public function setInvoiceInfo(?string $invoiceInfo): static
    {
        $this->invoiceInfo = $invoiceInfo;

        return $this;
    }
}
