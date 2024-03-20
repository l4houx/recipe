<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

trait HasIdentifyTrait
{
    #[Assert\Length(min: 4, max: 20)]
    #[Assert\NotBlank(groups: ['user:create'])]
    #[Assert\Regex(
        pattern: "/^(?:[\u00c0-\u01ffa-zA-Z'-]){2,}(?:\s[\u00c0-\u01ffa-zA-Z'-]{2,})+$/i",
        message: 'Invalid first name',
    )]
    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private string $firstname = '';

    #[Assert\Length(min: 4, max: 20)]
    #[Assert\NotBlank(groups: ['user:create'])]
    #[Assert\Regex(
        pattern: "/^(?:[\u00c0-\u01ffa-zA-Z'-]){2,}(?:\s[\u00c0-\u01ffa-zA-Z'-]{2,})+$/i",
        message: 'Invalid last name',
    )]
    #[ORM\Column(type: Types::STRING, length: 20)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
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
