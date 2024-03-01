<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasRatingTrait
{
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 5])]
    #[Assert\NotBlank]
    private int $rating;

    public function getRatingPercentage(): int|float
    {
        return ($this->rating / 5) * 100;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
}
