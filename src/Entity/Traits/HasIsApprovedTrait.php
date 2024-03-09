<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasIsApprovedTrait
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Assert\NotNull]
    private bool $isApproved = false;

    public function isApproved(): bool
    {
        return $this->isApproved;
    }

    public function getIsApproved(): bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): static
    {
        $this->isApproved = $isApproved;

        return $this;
    }
}
