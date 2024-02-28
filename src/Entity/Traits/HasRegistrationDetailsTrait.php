<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasRegistrationDetailsTrait
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $suspended = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $agreeTerms = false;

    #[ORM\Column(nullable: true)]
    private ?array $bill = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['default' => null])]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    private ?string $lastLoginIp = null;

    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function setSuspended(bool $suspended): static
    {
        $this->suspended = $suspended;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isAgreeTerms(): ?bool
    {
        return $this->agreeTerms;
    }

    public function setAgreeTerms(bool $agreeTerms): static
    {
        $this->agreeTerms = $agreeTerms;

        return $this;
    }

    public function getBill(): ?array
    {
        return $this->bill;
    }

    public function setBill(?array $bill): static
    {
        $this->bill = $bill;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function setLastLoginIp(?string $lastLoginIp): static
    {
        $this->lastLoginIp = $lastLoginIp;

        return $this;
    }
}
