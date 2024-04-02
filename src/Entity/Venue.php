<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Repository\VenueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
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
    #[Assert\Length(min: 5, max: 15)]
    #[Assert\Regex(
        pattern: '/^[A-Za-z0-9]{2}\d{3}$/',
        message: 'Invalid postal code.',
        groups: ['order']
    )]
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
    // #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    /**
     * @var collection<int, VenueImage>
     */
    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: VenueImage::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\ManyToOne(inversedBy: 'venues')]
    private ?VenueType $type = null;

    /**
     * @var collection<int, VenueSeatingPlan>
     */
    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: VenueSeatingPlan::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $seatingPlans;

    /**
     * @var collection<int, RecipeDate>
     */
    #[ORM\OneToMany(targetEntity: RecipeDate::class, mappedBy: 'venue')]
    private Collection $recipedates;

    /**
     * @var Collection<int, Amenity>
     */
    #[ORM\ManyToMany(targetEntity: Amenity::class, inversedBy: 'venues', cascade: ['persist', 'merge', 'merge'])]
    #[ORM\JoinTable(name: 'venue_amenity')]
    #[ORM\JoinColumn(name: 'venue_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'amenity_id', referencedColumnName: 'id')]
    private Collection $amenities;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->seatingPlans = new ArrayCollection();
        $this->recipedates = new ArrayCollection();
        $this->amenities = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, VenueImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(VenueImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setVenue($this);
        }

        return $this;
    }

    public function removeImage(VenueImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getVenue() === $this) {
                $image->setVenue(null);
            }
        }

        return $this;
    }

    public function getImagePlaceholder(string $size = 'default'): string
    {
        if ('small' == $size) {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAABGCAMAAABG8BK2AAAAS1BMVEUAAAD2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhGpYChqAAAAGHRSTlMAAgYHDyMmKT9JTlFSVVaAhZ2oqq3g+/1i504YAAAAwklEQVRYw+2XQQ7CIBREx1aLFkRtFef+J3UBERtNAMUF+t9mNuSFMJDwAUH4NTYXprmeVglNjoXkNqHJs1CnNWebIE9jkV7TpEZZ0wPojVUxOh8FGkcaAIZ0MUYfBZoQlmQM7aNAM9EpAMpxijH4gBQuhf9T4YMeOwDdqIenkMKlcCm89cIracIRLyk/4pebKi88XL8l5dcvPIb14RjYvfUYAvv49/ykKSuar2hmfWd+mBIieZoao0elQajSWCYI7XEDcpBQF5AyIN0AAAAASUVORK5CYII=';
        } else {
            return 'data:image/png;base64,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';
        }
    }

    public function getFirstImageOrPlaceholder(): string
    {
        if (count($this->images) > 0) {
            return $this->images[0]->getImagePath();
        } else {
            return $this->getImagePlaceholder();
        }
    }

    public function getType(): ?VenueType
    {
        return $this->type;
    }

    public function setType(?VenueType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, VenueSeatingPlan>
     */
    public function getSeatingPlans(): Collection
    {
        return $this->seatingPlans;
    }

    public function addSeatingPlan(VenueSeatingPlan $seatingPlan): static
    {
        if (!$this->seatingPlans->contains($seatingPlan)) {
            $this->seatingPlans->add($seatingPlan);
            $seatingPlan->setVenue($this);
        }

        return $this;
    }

    public function removeSeatingPlan(VenueSeatingPlan $seatingPlan): static
    {
        if ($this->seatingPlans->removeElement($seatingPlan)) {
            // set the owning side to null (unless already changed)
            if ($seatingPlan->getVenue() === $this) {
                $seatingPlan->setVenue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RecipeDate>
     */
    public function getRecipeDates(): Collection
    {
        return $this->recipedates;
    }

    public function addRecipeDate(RecipeDate $recipedate): static
    {
        if (!$this->recipedates->contains($recipedate)) {
            $this->recipedates->add($recipedate);
            $recipedate->setVenue($this);
        }

        return $this;
    }

    public function removeRecipeDate(RecipeDate $recipedate): static
    {
        if ($this->recipedates->removeElement($recipedate)) {
            // set the owning side to null (unless already changed)
            if ($recipedate->getVenue() === $this) {
                $recipedate->setVenue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Amenity>
     */
    public function getAmenities(): Collection
    {
        return $this->amenities;
    }

    public function addAmenity(Amenity $amenity): static
    {
        if (!$this->amenities->contains($amenity)) {
            $this->amenities->add($amenity);
        }

        return $this;
    }

    public function removeAmenity(Amenity $amenity): static
    {
        $this->amenities->removeElement($amenity);

        return $this;
    }
}
