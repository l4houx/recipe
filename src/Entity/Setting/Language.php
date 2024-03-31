<?php

namespace App\Entity\Setting;

use App\Entity\Recipe;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Repository\Setting\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class Language
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    public const LANG_LIMIT = HasLimit::LANG_LIMIT;

    #[ORM\Column(type: Types::STRING, length: 2)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2)]
    private string $code = '';

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\ManyToMany(targetEntity: Recipe::class, mappedBy: 'languages')]
    private Collection $recipes;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\ManyToMany(targetEntity: Recipe::class, mappedBy: 'subtitles')]
    private Collection $recipessubtitled;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
        $this->recipessubtitled = new ArrayCollection();
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
            $recipe->addLanguage($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            $recipe->removeLanguage($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecipessubtitled(): Collection
    {
        return $this->recipessubtitled;
    }

    public function addRecipessubtitled(Recipe $recipessubtitled): static
    {
        if (!$this->recipessubtitled->contains($recipessubtitled)) {
            $this->recipessubtitled->add($recipessubtitled);
            $recipessubtitled->addSubtitle($this);
        }

        return $this;
    }

    public function removeRecipessubtitled(Recipe $recipessubtitled): static
    {
        if ($this->recipessubtitled->removeElement($recipessubtitled)) {
            $recipessubtitled->removeSubtitle($this);
        }

        return $this;
    }
}
