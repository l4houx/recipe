<?php

namespace App\Entity\Traits;

use App\Entity\Country;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints as Assert;

trait HasAddressTrait
{
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $street2 = null;

    #[ORM\Column(type: Types::STRING, length: 15, nullable: true)]
    #[Assert\Length(min: 5, max: 15)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9]{2}\d{3}$/',
        message: 'Invalid postal code.',
        groups: ['order']
    )]
    private ?string $postalcode = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $state = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(min: 1, max: 50)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthdate = null;

    public function stringifyAddress(): string
    {
        $address = '';

        if ($this->street) {
            $address .= $this->street.' ';
        }

        if ($this->street2) {
            $address .= $this->street2.' ';
        }

        if ($this->city) {
            $address .= $this->city.' ';
        }

        if ($this->state) {
            $address .= $this->state.' ';
        }

        if ($this->postalcode) {
            $address .= $this->postalcode.' ';
        }

        if ($this->country) {
            $address .= $this->country->getName();
        }

        return $address;
    }

    public function getStreet(): string
    {
        return u($this->street)->upper()->toString();
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getStreet2(): ?string
    {
        return u($this->street2)->upper()->toString();
    }

    public function setStreet2(?string $street2): static
    {
        $this->street2 = $street2;

        return $this;
    }

    public function getPostalcode(): string
    {
        return u($this->postalcode)->upper()->toString();
    }

    public function setPostalcode(string $postalcode): static
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getCity(): string
    {
        return u($this->city)->upper()->toString();
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): string
    {
        return u($this->state)->upper()->toString();
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPhone(): ?string
    {
        return u($this->phone)->upper()->toString();
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTime $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }
}
