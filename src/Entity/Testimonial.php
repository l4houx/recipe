<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasRatingTrait;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Repository\TestimonialRepository;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoHeadlineAndSlugTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TestimonialRepository::class)]
#[UniqueEntity('headline')]
#[UniqueEntity('slug')]
class Testimonial
{
    use HasIdGedmoHeadlineAndSlugTrait;
    use HasContentTrait;
    use HasRatingTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    public const TESTIMONIAL_LIMIT = HasLimit::TESTIMONIAL_LIMIT;

    #[ORM\ManyToOne(inversedBy: 'testimonials')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull(groups: ['create', 'update'])]
    private bool $enabletestimonials = true;

    public function __toString(): string
    {
        return (string) $this->author->getUsername();
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

    public function isEnabletestimonials(): bool
    {
        return $this->enabletestimonials;
    }

    public function getEnabletestimonials(): bool
    {
        return $this->enabletestimonials;
    }

    public function setEnabletestimonials(bool $enabletestimonials): static
    {
        $this->enabletestimonials = $enabletestimonials;

        return $this;
    }
}
