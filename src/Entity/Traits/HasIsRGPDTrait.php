<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasIsRGPDTrait
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Assert\NotNull]
    private bool $isRGPD = false;

    public function isRGPD(): bool
    {
        return $this->isRGPD;
    }

    public function getIsRGPD(): bool
    {
        return $this->isRGPD;
    }

    public function setIsRGPD(bool $isRGPD): static
    {
        $this->isRGPD = $isRGPD;

        return $this;
    }
}
