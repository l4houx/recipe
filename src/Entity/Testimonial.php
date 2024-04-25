<?php

namespace App\Entity;

use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoHeadlineAndSlugTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRatingTrait;
use App\Repository\TestimonialRepository;
use Doctrine\ORM\Mapping as ORM;
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
}
