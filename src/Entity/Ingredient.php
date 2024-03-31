<?php

namespace App\Entity;

use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
#[UniqueEntity('name')]
#[UniqueEntity('slug')]
class Ingredient
{
    use HasIdGedmoNameSlugAssertTrait;

    #[ORM\OneToMany(targetEntity: Quantity::class, mappedBy: 'ingredient', orphanRemoval: true)]
    private Collection $quantities;

    public function __construct()
    {
        $this->quantities = new ArrayCollection();
    }

    /**
     * @return Collection<int, Quantity>
     */
    public function getQuantities(): Collection
    {
        return $this->quantities;
    }

    public function addQuantity(Quantity $quantity): static
    {
        if (!$this->quantities->contains($quantity)) {
            $this->quantities->add($quantity);
            $quantity->setIngredient($this);
        }

        return $this;
    }

    public function removeQuantity(Quantity $quantity): static
    {
        if ($this->quantities->removeElement($quantity)) {
            // set the owning side to null (unless already changed)
            if ($quantity->getIngredient() === $this) {
                $quantity->setIngredient(null);
            }
        }

        return $this;
    }
}
