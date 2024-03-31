<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasIsFeaturedTrait
{
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Assert\NotNull]
    private bool $isFeatured = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $featuredorder = null;

    public function isFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function getIsFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    public function getFeaturedorder(): ?int
    {
        return $this->featuredorder;
    }

    public function setFeaturedorder(?int $featuredorder): static
    {
        $this->featuredorder = $featuredorder;

        return $this;
    }
}
