<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasAuthorTrait
{
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $authors = null;

    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    #[Assert\Length(max: 5)]
    private ?string $year = null;

    public function getAuthors(): ?string
    {
        return $this->authors;
    }

    public function setAuthors(?string $authors): static
    {
        $this->authors = $authors;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): static
    {
        $this->year = $year;

        return $this;
    }
}
