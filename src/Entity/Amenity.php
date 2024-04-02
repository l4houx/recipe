<?php

namespace App\Entity;

use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIconTrait;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Repository\AmenityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: AmenityRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class Amenity
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasIconTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    /**
     * @var Collection<int, Venue>
     */
    #[ORM\ManyToMany(targetEntity: Venue::class, mappedBy: 'amenities')]
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
            $venue->addAmenity($this);
        }

        return $this;
    }

    public function removeVenue(Venue $venue): static
    {
        if ($this->venues->removeElement($venue)) {
            $venue->removeAmenity($this);
        }

        return $this;
    }
}
