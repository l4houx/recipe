<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\HasLimit;
use App\Entity\Setting\Language;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasTagTrait;
use App\Entity\Traits\HasLevelTrait;
use App\Entity\Traits\HasViewsTrait;
use App\Repository\RecipeRepository;
use App\Entity\Traits\HasAuthorTrait;
use App\Entity\Traits\HasContentTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasReferenceTrait;
use App\Entity\Setting\HomepageHeroSetting;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasSocialNetworksTrait;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Entity\Traits\HasIdGedmoTitleSlugAssertTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
#[Vich\Uploadable]
#[UniqueEntity('title')]
#[UniqueEntity('slug')]
class Recipe
{
    use HasIdGedmoTitleSlugAssertTrait;
    use HasContentTrait;
    use HasIsOnlineTrait;
    use HasViewsTrait;
    use HasLevelTrait;
    use HasSocialNetworksTrait;
    use HasReferenceTrait;
    use HasAuthorTrait;
    use HasTagTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    public const RECIPE_LIMIT = HasLimit::RECIPE_LIMIT;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'recipe_image', fileNameProperty: 'imageName')]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
        mimeTypesMessage: 'The file should be an image'
    )]
    #[Assert\NotNull(groups: ['create'])]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Positive()]
    #[Assert\LessThan(value: 1440)]
    private ?int $duration = null;

    #[ORM\ManyToOne(inversedBy: 'recipes', cascade: ['persist'])]
    private ?Category $category = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull(groups: ['create', 'update'])]
    private bool $enablereviews = true;

    /**
     * @var collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['remove'])]
    private Collection $reviews;

    /**
     * @var collection<int, Quantity>
     */
    #[ORM\OneToMany(targetEntity: Quantity::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['persist'])]
    #[Assert\Valid()]
    private Collection $quantities;

    /**
     * @var collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favorites', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'favorites')]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private Collection $addedtofavoritesby;

    #[ORM\ManyToOne(inversedBy: 'recipes', cascade: ['persist'])]
    private ?HomepageHeroSetting $isonhomepageslider = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $premium = false;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Country $country = null;

    /**
     * @var collection<int, RecipeImage>
     */
    #[ORM\OneToMany(targetEntity: RecipeImage::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $images;

    /**
     * @var Collection<int, Language>
     */
    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'recipes', cascade: ['persist', 'merge'])]
    #[ORM\JoinTable(name: 'recipe_language')]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'language_id', referencedColumnName: 'id')]
    private Collection $languages;

    /**
     * @var Collection<int, Language>
     */
    #[ORM\ManyToMany(targetEntity: Language::class, inversedBy: 'recipessubtitled', cascade: ['persist', 'merge'])]
    #[ORM\JoinTable(name: 'recipe_subtitle')]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'language_id', referencedColumnName: 'id')]
    private Collection $subtitles;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Assert\NotNull(groups: ['create', 'update'])]
    private bool $isShowattendees = true;

    public function __toString(): string
    {
        return sprintf('#%d %s', $this->getId(), $this->getTitle());
    }

    public function __construct()
    {
        $this->reference = $this->generateReference(10);
        $this->reviews = new ArrayCollection();
        $this->quantities = new ArrayCollection();
        $this->addedtofavoritesby = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->subtitles = new ArrayCollection();
    }

    public function hasContactAndSocialMedia(): bool
    {
        return $this->externallink
            || $this->phone || $this->email
            || $this->youtubeurl || $this->twitterurl
            || $this->instagramurl || $this->facebookurl
            || $this->googleplusurl || $this->linkedinurl
        ;
    }

    public function stringifyStatus(): string
    {
        if (!$this->restaurant->getUser()->isVerified()) {
            return 'Restaurant is disabled';
        } elseif (!$this->isOnline) {
            return 'Recipe is not published';
        } else {
            return 'On sale';
        }
    }

    public function stringifyStatusClass(): string
    {
        if (!$this->restaurant->getUser()->isVerified()) {
            return 'danger';
        } elseif (!$this->isOnline) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    public function isOnSale()
    {
        return
            $this->restaurant->getUser()->isVerified()()
            && $this->isOnline
        ;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setImageFile(File|UploadedFile|null $imageFile): static
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImagePath(): string
    {
        return '/images/recipe/'.$this->imageName;
    }

    public function getImagePlaceholder(string $size = 'default'): string
    {
        if ('small' == $size) {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAABGCAMAAABG8BK2AAAAXVBMVEUAAAD2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhHkayjUAAAAHnRSTlMABQ0PEBEcKixARkdJSk1WYmRmZ3R1eHl7f4WjsMpiv/ZRAAAAtUlEQVRYw+3X2Q7CIBCFYVqRLmoH3Oly3v8xvfCiJrYsSjRp578l+RJIIIMQHLfEMiKiYmKhICLKQhkJAHZiwQKAXC2zPWjTAsBg3hsAoDW6zj3KDWEdncoZSOBsEJ5jX/sIpp5ndASj5xkTwRhm/s101lcXwijv9VXMMPMzRlZj5edM9XKTemaYWTNT9mNXfm++YzQANMpX45m2Es1+iSZRcQlWTs7TuydRnn8GX3qXC45bXA+ADIuZ4XkIYQAAAABJRU5ErkJggg==';
        } else {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAV4AAAFeCAMAAAD69YcoAAAA9lBMVEUAAAD2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhEZWD6+AAAAUXRSTlMAAQIDBAYJDQ4QExQVFhcYGhseHyAhKi0uLzAxMjQ6Ozw+P0BERUZHSUpPUVVWV1hZYmRmcXN0dYOFiYuXmJq3ur7HyNHT1dfZ3Obo8fX3+fvgzUWiAAADeklEQVR42u3cSVNTQRSA0RcBgQhOKCg4MImoJPKCA+BAQGVUof//n3Gpm7iQCt3ver51qjo5i5fu26lUlSRJkiRJkiRJkiRJkiRJkiRJkiRJkiRJkiRdVdc/DGjrChbfGrT49Si8k2lA+1ew+P6gxSfx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjHbj5a79S93+0M+oSnveF3OmjxnT9eVG+szbWvNcD23puj1NT2OuNF246+PkvNrj9bLG6r+yM1v4O7Zere/ppitD1SoO7LFKbjdnG6uylQF/OFPXY/pVitFsX7MSW+Q+tdilc5z4enAXXTRSnfbxPnEXnTcSH7s36K2XYRuk9S1Eo4v7XOwvIeFMC7nOJWwHznMDBvP7vuVIpc9vlvLzRvJzfvQWjevdz3Eyl2me/fpoPzZj4ZzwfnncvLux6cdy0vbzc470Ze3jo4b23bO8x6ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168/8J7st+kTprGu1g1qUW8ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168ePHixYsXL168eIfQ5MplWsL791Yu9c8453jx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjx4sWLFy9evHjxNp136fwyfcebJbx48eLFixcvXrx48eLFixcvXrx48eLFixcvXrx48eLFixfvf8BbD3pfu4tNanfQx6jz8nZT7Dby8q4H513LyzsfnPdBXt7p4LztvLyjwXmvZd7SfAmtu5d7x9gLzdvJzTsVmnc8+4HnMLBuP/95cjkw72x+3tZZWN0vJYxDnoblnSli3NQPqrtTxjRv4jyk7vFI6dPSJnfRrkrpfUDehYKm/Z/C6a6WdJnS+hxM91lht1UfQj13F6rSehVoz9CuyuvOtyj73ZGqxFqbPyOchGeqUhvdbPoAoj9bFd39t0fNte1ONOAnL2O3Hr/o1r0mVXeeP7wxUkmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEnSsPoFIVJb/voL1VsAAAAASUVORK5CYII=';
        }
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isEnablereviews(): bool
    {
        return $this->enablereviews;
    }

    public function getEnablereviews(): bool
    {
        return $this->enablereviews;
    }

    public function setEnablereviews(bool $enablereviews): static
    {
        $this->enablereviews = $enablereviews;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setRecipe($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getRecipe() === $this) {
                $review->setRecipe(null);
            }
        }

        return $this;
    }

    public function isRatedBy(User $user): Review
    {
        /** @var Review $review */
        foreach ($this->reviews as $review) {
            if ($review->getAuthor() === $user) {
                return $review;
            }
        }

        return false;
    }

    public function getRatingsPercentageForRating($rating): int|float
    {
        if (!$this->countVisibleReviews()) {
            return 0;
        }

        return round(($this->getRatingsCountForRating($rating) / $this->countVisibleReviews()) * 100, 1);
    }

    public function getRatingsCountForRating($rating): int
    {
        if (!$this->countVisibleReviews()) {
            return 0;
        }

        $ratingCount = 0;

        /** @var Review $review */
        foreach ($this->reviews as $review) {
            if ($review->getVisible() && $review->getRating() === $rating) {
                ++$ratingCount;
            }
        }

        return $ratingCount;
    }

    public function getRatingAvg(): int|float
    {
        if (!$this->countVisibleReviews()) {
            return 0;
        }

        $ratingAvg = 0;

        /** @var Review $review */
        foreach ($this->reviews as $review) {
            if ($review->getVisible()) {
                $ratingAvg += $review->getRating();
            }
        }

        return round($ratingAvg / $this->countVisibleReviews(), 1);
    }

    public function getRatingPercentage(): int|float
    {
        if (!$this->countVisibleReviews()) {
            return 0;
        }

        $ratingPercentage = 0;

        /** @var Review $review */
        foreach ($this->reviews as $review) {
            if ($review->getVisible()) {
                $ratingPercentage += $review->getRatingPercentage();
            }
        }

        return round($ratingPercentage / $this->countVisibleReviews(), 1);
    }

    public function countVisibleReviews(): int
    {
        $count = 0;

        /** @var Review $review */
        foreach ($this->reviews as $review) {
            if ($review->getVisible()) {
                ++$count;
            }
        }

        return $count;
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
            $quantity->setRecipe($this);
        }

        return $this;
    }

    public function removeQuantity(Quantity $quantity): static
    {
        if ($this->quantities->removeElement($quantity)) {
            // set the owning side to null (unless already changed)
            if ($quantity->getRecipe() === $this) {
                $quantity->setRecipe(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAddedtofavoritesby(): Collection
    {
        return $this->addedtofavoritesby;
    }

    public function addAddedtofavoritesby(User $addedtofavoritesby): self
    {
        if (!$this->addedtofavoritesby->contains($addedtofavoritesby)) {
            $this->addedtofavoritesby->add($addedtofavoritesby);
        }

        return $this;
    }

    public function removeAddedtofavoritesby(User $addedtofavoritesby): self
    {
        $this->addedtofavoritesby->removeElement($addedtofavoritesby);

        return $this;
    }

    public function isAddedToFavoritesBy(User $user): bool
    {
        return $this->addedtofavoritesby->contains($user);
    }

    public function getIsonhomepageslider(): ?HomepageHeroSetting
    {
        return $this->isonhomepageslider;
    }

    public function setIsonhomepageslider(?HomepageHeroSetting $isonhomepageslider): static
    {
        $this->isonhomepageslider = $isonhomepageslider;

        return $this;
    }

    public function getPremium(): bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): static
    {
        $this->premium = $premium;

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

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, RecipeImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(RecipeImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setRecipe($this);
        }

        return $this;
    }

    public function removeImage(RecipeImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getRecipe() === $this) {
                $image->setRecipe(null);
            }
        }

        return $this;
    }

    public function displayLanguages(): string
    {
        $languages = '';

        if (\count($this->languages) > 0) {
            foreach ($this->languages as $language) {
                $languages .= $language->getName().', ';
            }
        }

        return rtrim($languages, ', ');
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        $this->languages->removeElement($language);

        return $this;
    }

    public function displaySubtitles(): string
    {
        $subtitles = '';

        if (\count($this->subtitles) > 0) {
            foreach ($this->subtitles as $subtitle) {
                $subtitles .= $subtitle->getName().', ';
            }
        }

        return rtrim($subtitles, ', ');
    }

    /**
     * @return Collection<int, Language>
     */
    public function getSubtitles(): Collection
    {
        return $this->subtitles;
    }

    public function addSubtitle(Language $subtitle): static
    {
        if (!$this->subtitles->contains($subtitle)) {
            $this->subtitles->add($subtitle);
        }

        return $this;
    }

    public function removeSubtitle(Language $subtitle): static
    {
        $this->subtitles->removeElement($subtitle);

        return $this;
    }

    public function isShowattendees(): bool
    {
        return $this->isShowattendees;
    }

    public function setIsShowattendees(bool $isShowattendees): static
    {
        $this->isShowattendees = $isShowattendees;

        return $this;
    }
}
