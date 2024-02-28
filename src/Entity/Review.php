<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReviewRepository;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasTimestampTrait;
use App\Entity\Traits\HasIdHeadlineAndSlugTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[UniqueEntity('headline')]
#[UniqueEntity('slug')]
class Review
{
    use HasIdHeadlineAndSlugTrait;
    use HasContentTrait;
    use HasTimestampTrait;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank]
    private int $rating;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isVisible = true;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recipe $recipe = null;

    public function __construct()
    {
        $this->isVisible = true;
    }

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

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function getVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): static
    {
        $this->recipe = $recipe;

        return $this;
    }
}
