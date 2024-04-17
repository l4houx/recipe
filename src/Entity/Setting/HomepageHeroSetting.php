<?php

namespace App\Entity\Setting;

use App\Entity\Recipe;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTrait;
use App\Entity\User;
use App\Repository\Setting\HomepageHeroSettingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: HomepageHeroSettingRepository::class)]
#[Vich\Uploadable]
class HomepageHeroSetting
{
    use HasIdTrait;
    use HasGedmoTimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paragraph = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'homepage_hero_custom_background', fileNameProperty: 'customBackgroundName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
        mimeTypesMessage: 'The file should be an image'
    )]
    private ?File $customBackgroundFile = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $customBackgroundName = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $show_search_box = null;

    /**
     * @var Collection<int, Recipe>
     */
    #[ORM\OneToMany(targetEntity: Recipe::class, mappedBy: 'isonhomepageslider', cascade: ['persist'])]
    private Collection $recipes;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'isrestaurantonhomepageslider')]
    private Collection $restaurants;

    public function __construct()
    {
        $this->recipes = new ArrayCollection();
        $this->restaurants = new ArrayCollection();
    }

    public function clearRecipes(): void
    {
        $this->recipes->clear();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getParagraph(): ?string
    {
        return $this->paragraph;
    }

    public function setParagraph(?string $paragraph): static
    {
        $this->paragraph = $paragraph;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = trim($content ?: '');

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setCustomBackgroundFile(File|UploadedFile|null $customBackgroundFile)
    {
        $this->customBackgroundFile = $customBackgroundFile;

        if (null !== $customBackgroundFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getCustomBackgroundFile(): ?File
    {
        return $this->customBackgroundFile;
    }

    public function getCustomBackgroundName(): ?string
    {
        return $this->customBackgroundName;
    }

    public function setCustomBackgroundName(?string $customBackgroundName): static
    {
        $this->customBackgroundName = $customBackgroundName;

        return $this;
    }

    public function getCustomBackgroundPath(): string
    {
        return '/public/images/home/'.$this->customBackgroundName;
    }

    public function isShowSearchBox(): ?bool
    {
        return $this->show_search_box;
    }

    public function getShowSearchBox(): ?bool
    {
        return $this->show_search_box;
    }

    public function setShowSearchBox(?bool $show_search_box): static
    {
        $this->show_search_box = $show_search_box;

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
            $recipe->setIsonhomepageslider($this);
        }

        return $this;
    }

    public function removeRecipe(Recipe $recipe): static
    {
        if ($this->recipes->removeElement($recipe)) {
            // set the owning side to null (unless already changed)
            if ($recipe->getIsonhomepageslider() === $this) {
                $recipe->setIsonhomepageslider(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(User $restaurant): static
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants->add($restaurant);
            $restaurant->setIsrestaurantonhomepageslider($this);
        }

        return $this;
    }

    public function removeRestaurant(User $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getIsrestaurantonhomepageslider() === $this) {
                $restaurant->setIsrestaurantonhomepageslider(null);
            }
        }

        return $this;
    }
}
