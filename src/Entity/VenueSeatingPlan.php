<?php

namespace App\Entity;

use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Repository\VenueSeatingPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenueSeatingPlanRepository::class)]
class VenueSeatingPlan
{
    use HasIdGedmoNameSlugAssertTrait;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $design = null;

    #[ORM\ManyToOne(inversedBy: 'seatingPlans')]
    private ?Venue $venue = null;

    /**
     * @var Collection<int, RecipeDate>
     */
    #[ORM\OneToMany(mappedBy: 'seatingPlan', targetEntity: RecipeDate::class, cascade: ['persist', 'remove'])]
    private Collection $recipeDates;

    public function __construct()
    {
        $this->recipeDates = new ArrayCollection();
    }

    public function getDesign(): ?array
    {
        return $this->design;
    }

    public function setDesign(?array $design): static
    {
        $this->design = $design;

        return $this;
    }

    public function getSectionsNamesArray(): array
    {
        $sectionsNamesArray = [];
        foreach ($this->design['sections'] as $section) {
            $sectionsNamesArray[$section['name']] = $section['name'];
        }
        ksort($sectionsNamesArray);

        return $sectionsNamesArray;
    }

    public function getSectionsSeatsQuantityArray(): array
    {
        $sectionsSeatsQuantityArray = [];
        foreach ($this->design['sections'] as $section) {
            $sectionsSeatsQuantityArray[$section['name']] = $this->getSectionSeatsCount($section);
        }
        ksort($sectionsSeatsQuantityArray);

        return $sectionsSeatsQuantityArray;
    }

    public function getSectionSeatsCount(mixed $section): mixed
    {
        $count = 0;
        foreach ($section['rows'] as $row) {
            $count += $this->getRowSeatsCount($row);
        }

        return $count;
    }

    public function getRowSeatsCount(mixed $row): int|float
    {
        return (intval($row['seatsEndNumber']) - intval($row['seatsStartNumber'])) + 1 - count($row['disabledSeats']) - count($row['hiddenSeats']);
    }

    public function countTotalSeats(): mixed
    {
        $count = 0;
        foreach ($this->design['sections'] as $section) {
            foreach ($section['rows'] as $row) {
                $count += $this->getRowSeatsCount($row);
            }
        }

        return $count;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * @return Collection<int, RecipeDate>
     */
    public function getRecipeDates(): Collection
    {
        return $this->recipeDates;
    }

    public function addRecipeDate(RecipeDate $recipeDate): static
    {
        if (!$this->recipeDates->contains($recipeDate)) {
            $this->recipeDates->add($recipeDate);
            $recipeDate->setSeatingPlan($this);
        }

        return $this;
    }

    public function removeRecipeDate(RecipeDate $recipeDate): static
    {
        if ($this->recipeDates->removeElement($recipeDate)) {
            // set the owning side to null (unless already changed)
            if ($recipeDate->getSeatingPlan() === $this) {
                $recipeDate->setSeatingPlan(null);
            }
        }

        return $this;
    }
}
