<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class Payment
{
    use HasIdTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    #[Assert\Length(min: 2, max: 20)]
    #[Assert\Regex(
        pattern: "/^(?:[\u00c0-\u01ffa-zA-Z'-]){2,}(?:\s[\u00c0-\u01ffa-zA-Z'-]{2,})+$/i",
        message: 'Invalid first name',
    )]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    #[Assert\Length(min: 2, max: 20)]
    #[Assert\Regex(
        pattern: "/^(?:[\u00c0-\u01ffa-zA-Z'-]){2,}(?:\s[\u00c0-\u01ffa-zA-Z'-]{2,})+$/i",
        message: 'Invalid last name',
    )]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $street2 = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $state = null;

    #[ORM\Column(type: Types::STRING, length: 15, nullable: true)]
    #[Assert\Length(min: 5, max: 15)]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9]{2}\d{3}$/',
        message: 'Invalid postal code.',
        groups: ['order']
    )]
    private ?string $postalcode = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Order $order = null;

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getStreet2(): ?string
    {
        return $this->street2;
    }

    public function setStreet2(?string $street2): static
    {
        $this->street2 = $street2;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(?string $postalcode): static
    {
        $this->postalcode = $postalcode;

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
        if ($this->postalcode) {
            $address .= $this->postalcode.' ';
        }
        if ($this->state) {
            $address .= $this->state.' ';
        }
        if ($this->country) {
            $address .= $this->country->getName();
        }

        return $address;
    }

    public function hasBillingInformation(): bool
    {
        return $this->firstname || $this->lastname || $this->clientEmail || $this->stringifyAddress();
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
    }
}
