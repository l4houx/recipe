<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasRegistrationDetailsTrait
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $isSuspended = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $isAgreeTerms = false;

    #[ORM\Column(nullable: true)]
    private ?array $bill = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['default' => null])]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: Types::STRING, nullable: true, options: ['default' => null])]
    private ?string $lastLoginIp = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $registerDuration = 0;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $resetToken = null;

    public function isSuspended(): bool
    {
        return $this->isSuspended;
    }

    public function getIsSuspended(): bool
    {
        return $this->isSuspended;
    }

    public function setIsSuspended(bool $isSuspended): static
    {
        $this->isSuspended = $isSuspended;

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
        return $this->isAgreeTerms;
    }

    public function setIsAgreeTerms(bool $isAgreeTerms): static
    {
        $this->isAgreeTerms = $isAgreeTerms;

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

    public function getRegisterDuration(): int
    {
        return $this->registerDuration;
    }

    public function setRegisterDuration(int $registerDuration): static
    {
        $this->registerDuration = $registerDuration;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function hasValidatedReset(): bool
    {
        return null === $this->resetToken;
    }

    public function canLogin(): bool
    {
        return !$this->isSuspended() && null === $this->getResetToken();
    }
}
