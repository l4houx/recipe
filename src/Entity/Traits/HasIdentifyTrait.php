<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

trait HasIdentifyTrait
{
    #[Assert\Length(min: 4, max: 20)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: "/^(?:[\u00c0-\u01ffa-zA-Z'-]){2,}(?:\s[\u00c0-\u01ffa-zA-Z'-]{2,})+$/i",
        message: 'Prénom invalide.',
    )]
    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $firstname = '';

    #[Assert\Length(min: 4, max: 20)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: "/^(?:[\u00c0-\u01ffa-zA-Z'-]){2,}(?:\s[\u00c0-\u01ffa-zA-Z'-]{2,})+$/i",
        message: 'Nom de famille invalide.',
    )]
    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $lastname = '';

    public function getFullName(): string
    {
        return u(sprintf('%s %s', $this->firstname, $this->lastname))->upper()->toString();
    }

    public function getFirstname(): ?string
    {
        return u($this->firstname)->upper()->toString();
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return u($this->lastname)->upper()->toString();
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }
}
