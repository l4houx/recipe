<?php

namespace App\Entity;

use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoHeadlineAndSlugTrait;
use App\Entity\Traits\HasRatingTrait;
use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[UniqueEntity('headline')]
#[UniqueEntity('slug')]
class Review
{
    use HasIdGedmoHeadlineAndSlugTrait;
    use HasRatingTrait;
    use HasContentTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isVisible = true;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    // #[ORM\JoinColumn(nullable: false)]
    private ?Recipe $recipe = null;

    public function __construct()
    {
        $this->isVisible = true;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function getIsVisible(): bool
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
