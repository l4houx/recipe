<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasLimit;
use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class Country
{
    use HasIdTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    public const COUNTRY_LIMIT = HasLimit::COUNTRY_LIMIT;

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Assert\NotBlank(message: "Please don't leave your name blank!")]
    #[Assert\Length(
        min: 1,
        max: 50,
        minMessage: 'The name is too short ({{ limit }} characters minimum)',
        maxMessage: 'The name is too long ({ limit } characters maximum)'
    )]
    #[Gedmo\Translatable]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    #[Gedmo\Slug(fields: ['name'], unique: true, updatable: true)]
    private string $slug = '';

    #[ORM\Column(type: Types::STRING, length: 2)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2)]
    private string $code = '';

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(mappedBy: 'country', targetEntity: Recipe::class)]
    private Collection $recipes;

    /**
     * @var Collection<int, Restaurant>
     */
    #[ORM\OneToMany(mappedBy: 'country', targetEntity: Restaurant::class)]
    private Collection $restaurants;

    /**
     * @var Collection<int, Venue>
     */
    #[ORM\OneToMany(mappedBy: 'country', targetEntity: Venue::class)]
    private Collection $venues;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
        $this->restaurants = new ArrayCollection();
        $this->venues = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getName() ?: '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipes(): Collection
    {
        return $this->recipes;
    }

    public function addRecipe(Recipe $recipe): static
    {
        if (!$this->recipes->contains($recipe)) {
            $this->recipes->add($recipe);
            $recipe->setCountry($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getCountry() === $this) {
                $recipe->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Restaurant>
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): static
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants->add($restaurant);
            $restaurant->setCountry($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getCountry() === $this) {
                $restaurant->setCountry(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Venue>
     */
    public function getVenues(): Collection
    {
        return $this->venues;
    }

    public function addVenue(Venue $venue): static
    {
        if (!$this->venues->contains($venue)) {
            $this->venues->add($venue);
            $venue->setCountry($this);
        }

        return $this;
    }

    public function removeVenue(Venue $venue): static
    {
        if ($this->venues->removeElement($venue)) {
            // set the owning side to null (unless already changed)
            if ($venue->getCountry() === $this) {
                $venue->setCountry(null);
            }
        }

        return $this;
    }
}
