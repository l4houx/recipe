<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasIsPortfolioTrait
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Assert\NotNull]
    private bool $isPortfolio = false;

    public function isPortfolio(): bool
    {
        return $this->isPortfolio;
    }

    public function getIsPortfolio(): bool
    {
        return $this->isPortfolio;
    }

    public function setIsPortfolio(bool $isPortfolio): static
    {
        $this->isPortfolio = $isPortfolio;

        return $this;
    }
}
