<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Repository\VenueTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: VenueTypeRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class VenueType
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    /**
     * @var collection<int, Venue>
     */
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Venue::class)]
    private Collection $venues;

    public function __construct()
    {
        $this->venues = new ArrayCollection();
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
            $venue->setType($this);
        }

        return $this;
    }

    public function removeVenue(Venue $venue): static
    {
        if ($this->venues->removeElement($venue)) {
            // set the owning side to null (unless already changed)
            if ($venue->getType() === $this) {
                $venue->setType(null);
            }
        }

        return $this;
    }
}
