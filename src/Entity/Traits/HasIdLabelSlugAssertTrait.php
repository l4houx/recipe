<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasIdLabelSlugAssertTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: "Please don't leave your label blank!")]
    #[Assert\Length(
        min: 4,
        max: 128,
        minMessage: 'The label is too short ({{ limit }} characters minimum)',
        maxMessage: 'The label is too long ({ limit } characters maximum)'
    )]
    private string $label = '';

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(
        min: 4,
        max: 128,
        minMessage: 'The slug is too short ({{ limit }} characters minimum)',
        maxMessage: 'The slug is too long ({ limit } characters maximum)'
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Invalid slug.',
    )]
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
