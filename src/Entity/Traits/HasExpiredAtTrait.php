<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasExpiredAtTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $expiresAt = null;

    public function getExpiresAt(): ?\DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTime $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTime;
    }
}
