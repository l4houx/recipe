<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VenueRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VenueRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class Venue
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $neighborhoods = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    private string $description = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $pricing = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500)]
    private ?string $availibility = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isShowmap = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isQuoteform = true;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private string $street = '';

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $street2 = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private string $city = '';

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private string $state = '';

    #[ORM\Column(type: Types::STRING, length: 15)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 15)]
    private string $postalcode = '';

    #[ORM\ManyToOne(inversedBy: 'venues')]
    #[Assert\NotNull]
    private ?Country $country = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $lat = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $lng = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull]
    private bool $isListedondirectory = true;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $contactemail = null;

    #[ORM\ManyToOne(inversedBy: 'venues')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getNeighborhoods(): ?string
    {
        return $this->neighborhoods;
    }

    public function setNeighborhoods(?string $neighborhoods): static
    {
        $this->neighborhoods = $neighborhoods;

        return $this;
    }

    public function getPricing(): ?string
    {
        return $this->pricing;
    }

    public function setPricing(?string $pricing): static
    {
        $this->pricing = $pricing;

        return $this;
    }

    public function getAvailibility(): ?string
    {
        return $this->availibility;
    }

    public function setAvailibility(?string $availibility): static
    {
        $this->availibility = $availibility;

        return $this;
    }

    public function isIsShowmap(): bool
    {
        return $this->isShowmap;
    }

    public function setIsShowmap(bool $isShowmap): static
    {
        $this->isShowmap = $isShowmap;

        return $this;
    }

    public function isIsQuoteform(): bool
    {
        return $this->isQuoteform;
    }

    public function setIsQuoteform(bool $isQuoteform): static
    {
        $this->isQuoteform = $isQuoteform;

        return $this;
    }

    public function stringifyAddress(): string
    {
        $address = $this->street;

        if ('' !== $this->street2 && null !== $this->street2) {
            $address .= ', '.$this->street2;
        }

        $address .= ' '.$this->postalcode.' '.$this->city.', '.$this->state.', '.$this->country->getName();

        return $address;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
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

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getPostalcode(): string
    {
        return $this->postalcode;
    }

    public function setPostalcode(string $postalcode): static
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

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): static
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function setLng(?string $lng): static
    {
        $this->lng = $lng;

        return $this;
    }

    public function isIsListedondirectory(): bool
    {
        return $this->isListedondirectory;
    }

    public function setIsListedondirectory(bool $isListedondirectory): static
    {
        $this->isListedondirectory = $isListedondirectory;

        return $this;
    }

    public function getContactemail(): ?string
    {
        return $this->contactemail;
    }

    public function setContactemail(?string $contactemail): static
    {
        $this->contactemail = $contactemail;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }
}
