<?php

namespace App\Entity\Traits;

use App\Validator\BanWord;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait HasIdGedmoLabelSlugAssertTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 128)]
    #[Assert\NotBlank(message: "Please don't leave your label blank!")]
    #[Assert\Length(
        min: 4,
        max: 128,
        minMessage: 'The label is too short ({{ limit }} characters minimum)',
        maxMessage: 'The label is too long ({ limit } characters maximum)'
    )]
    #[BanWord()]
    #[Gedmo\Translatable]
    private string $label = '';

    #[ORM\Column(type: Types::STRING, length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['label'], unique: true, updatable: true)]
    private string $slug = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
