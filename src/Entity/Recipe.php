<?php

namespace App\Entity;

use App\Entity\Setting\HomepageHeroSetting;
use App\Entity\Setting\Language;
use App\Entity\Traits\HasAuthorTrait;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoTitleSlugAssertTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasLevelTrait;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasReferenceTrait;
use App\Entity\Traits\HasSocialNetworksTrait;
use App\Entity\Traits\HasTagTrait;
use App\Entity\Traits\HasViewsTrait;
use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

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

    /**
     * @var Collection<int, Audience>
     */
    #[ORM\ManyToMany(targetEntity: Audience::class, inversedBy: 'recipes', cascade: ['persist', 'merge'])]
    #[ORM\JoinTable(name: 'recipe_audience')]
    #[ORM\JoinColumn(name: 'recipe_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'audience_id', referencedColumnName: 'id')]
    private Collection $audiences;

    /**
     * @var collection<int, RecipeDate>
     */
    #[ORM\OneToMany(targetEntity: RecipeDate::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\OrderBy(['startdate' => 'ASC'])]
    #[Assert\Valid(groups: ['create', 'update'])]
    private Collection $recipedates;

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
        $this->audiences = new ArrayCollection();
        $this->recipedates = new ArrayCollection();
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
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAABGCAMAAABG8BK2AAAA/1BMVEUAAAD2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhH2dhFzESFhAAAAVHRSTlMAAQIDBQYICQsNDhAREhQVGBobISQmLC0wMTQ1ODlAQkNLT1xdY2ZpbXR1d3mDhoiLjpGSlZeYnaCjpaaqtLq8yMrMzs/R1d7k5ujp6+3z9ff5+/1JGcHEAAABMUlEQVRYw+3W2U4CQRCF4dMzAqO47zvuu6iAorjgDjpuQL3/s3jjBTCkulLpRBP7f4AvmaqeTgM+n8/3Gw2un5R/Km6NKRGzRx2VAhWTp66uNMoUJcopmLMk86BgXpMMKabTQ6EoaM9oma7i3YwLhuhjwglDXwNOGDp3w1Dohsm6YYb/PVOci0Rnn2Oa2ykV0clU+9VIG1MyANC3tHNaZjqa4ZlLAyB9aJ91xDF3IYCFhl2JDcO8ZACsSBY/yXxUYwQ9L/dkq9yIpwFkPwXKPrepNQCpmkCpcAs/AGBuBMp9yDAXAFAQKDFzs9NtAGBToDRHmVNcTwNYlCxpnvsZhgCMtwTKBixFbwLl2KaEjwLl2noN5QVKxf46sA/mfdmKALlnjmg9FWZ1zzifz+f7c30DdESL++xXlRkAAAAASUVORK5CYII=';
        } else {
            return 'data:image/png;base64,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';
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
            if ($review->getIsVisible() && $review->getRating() === $rating) {
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
            if ($review->getIsVisible()) {
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
            if ($review->getIsVisible()) {
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
            if ($review->getIsVisible()) {
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

    public function displayAudiences(): string
    {
        $audiences = '';

        if (count($this->audiences) > 0) {
            foreach ($this->audiences as $audience) {
                $audiences .= $audience->getName().', ';
            }
        }

        return rtrim($audiences, ', ');
    }

    /**
     * @return Collection<int, Audience>
     */
    public function getAudiences(): Collection
    {
        return $this->audiences;
    }

    public function addAudience(Audience $audience): static
    {
        if (!$this->audiences->contains($audience)) {
            $this->audiences->add($audience);
        }

        return $this;
    }

    public function removeAudience(Audience $audience): static
    {
        $this->audiences->removeElement($audience);

        return $this;
    }

    public function hasRecipeDateWithSeatingPlan(): bool
    {
        foreach ($this->recipedates as $recipeDate) {
            if ($recipeDate->getHasSeatingPlan()) {
                return true;
            }
        }

        return false;
    }

    public function getSales(string $role = 'all', string $user = 'all', bool $formattedForPayoutApproval = false, bool $includeFees = false): mixed
    {
        $sum = 0;
        foreach ($this->recipedates as $recipeDate) {
            $sum += $recipeDate->getSales($role, $user, $formattedForPayoutApproval, $includeFees);
        }

        return $sum;
    }

    public function getSubscriptionPricePercentageCutSum(string $role = 'all'): mixed
    {
        $sum = 0;
        foreach ($this->recipedates as $recipeDate) {
            $sum += $recipeDate->getSubscriptionPricePercentageCutSum($role);
        }

        return $sum;
    }

    public function getTotalOrderElementsQuantitySum(int $status = 1, string $user = 'all', string $role = 'all'): mixed
    {
        $sum = 0;
        foreach ($this->recipedates as $recipeDate) {
            $sum += $recipeDate->getOrderElementsQuantitySum($status, $user, $role);
        }

        return $sum;
    }

    public function getTotalCheckInPercentage()
    {
        if (0 == count($this->recipedates)) {
            return 0;
        }
        $recipeDatesCheckInPercentageSum = 0;
        foreach ($this->recipedates as $recipeDate) {
            $recipeDatesCheckInPercentageSum += $recipeDate->getTotalCheckInPercentage();
        }

        return round($recipeDatesCheckInPercentageSum / count($this->recipedates));
    }

    public function getTotalSalesPercentage()
    {
        if (0 == count($this->recipedates)) {
            return 0;
        }
        $recipeDatesSalesPercentageSum = 0;
        foreach ($this->recipedates as $recipeDate) {
            $recipeDatesSalesPercentageSum += $recipeDate->getTotalSalesPercentage();
        }

        return round($recipeDatesSalesPercentageSum / count($this->recipedates));
    }

    public function stringifyStatus(): string
    {
        if (!$this->restaurant->getUser()->isVerified()) {
            return 'Restaurant is disabled';
        } elseif (!$this->isOnline) {
            return 'Recipe is not published';
        } elseif (!$this->hasAnRecipeDateOnSale()) {
            return 'No recipes on sale';
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
        } elseif (!$this->hasAnRecipeDateOnSale()) {
            return 'info';
        } else {
            return 'success';
        }
    }

    public function isOnSale(): bool
    {
        return
        $this->hasAnRecipeDateOnSale()
            && $this->restaurant->getUser()->isVerified()()
            && $this->isOnline
        ;
    }

    public function getOrderElementsQuantitySum(int $status = 1): mixed
    {
        $sum = 0;
        foreach ($this->recipedates as $recipedate) {
            $sum += $recipedate->getOrderElementsQuantitySum($status);
        }

        return $sum;
    }

    public function hasTwoOrMoreRecipeDatesOnSale(): bool
    {
        $count = 0;
        foreach ($this->recipedates as $recipedate) {
            if ($recipedate->isOnSale()) {
                ++$count;
            }
        }

        return $count >= 2 ? true : false;
    }

    public function hasAnRecipeDateOnSale(): bool
    {
        foreach ($this->recipedates as $recipedate) {
            if ($recipedate->isOnSale()) {
                return true;
            }
        }

        return false;
    }

    public function getFirstOnSaleRecipeDate(): ?RecipeDate
    {
        foreach ($this->recipedates as $recipedate) {
            if ($recipedate->isOnSale()) {
                return $recipedate;
            }
        }

        return null;
    }

    public function isFree(): bool
    {
        foreach ($this->recipedates as $recipedate) {
            if (!$recipedate->isFree()) {
                return false;
            }
        }

        return true;
    }

    public function getCheapestSubscription(): ?RecipeSubscription
    {
        if (!$this->hasAnRecipeDateOnSale()) {
            return null;
        }
        $cheapestsubscription = $this->getFirstOnSaleRecipeDate()->getCheapestSubscription();
        foreach ($this->recipedates as $recipedate) {
            if ($recipedate->isOnSale()) {
                if ($recipedate->getCheapestSubscription()->getSalePrice() < $cheapestsubscription->getSalePrice()) {
                    $cheapestsubscription = $recipedate->getCheapestSubscription();
                }
            }
        }

        return $cheapestsubscription;
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
            $recipedate->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeDate(RecipeDate $recipedate): static
    {
        if ($this->recipedates->removeElement($recipedate)) {
            // set the owning side to null (unless already changed)
            if ($recipedate->getRecipe() === $this) {
                $recipedate->setRecipe(null);
            }
        }

        return $this;
    }
}
