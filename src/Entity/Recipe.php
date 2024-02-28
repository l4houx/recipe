<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasViewsTrait;
use App\Repository\RecipeRepository;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasTimestampTrait;
use Symfony\Component\HttpFoundation\File\File;
use App\Entity\Traits\HasIdTitleSlugAssertTrait;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[Vich\Uploadable]
#[UniqueEntity('title')]
#[UniqueEntity('slug')]
class Recipe
{
    use HasIdTitleSlugAssertTrait;
    use HasContentTrait;
    use HasViewsTrait;
    use HasTimestampTrait;

    public const RECIPE_LIMIT = HasLimit::RECIPE_LIMIT;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'recipe_image', fileNameProperty: 'thumbnail')]
    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/jpg', 'image/gif', 'image/png'],
        mimeTypesMessage: 'The file should be an image'
    )]
    private ?File $thumbnailFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $thumbnail = null;

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

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['publishedAt' => 'DESC'])]
    private Collection $comments;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'recipe', orphanRemoval: true, cascade: ['remove'])]
    private Collection $reviews;

    public function __toString(): string
    {
        return sprintf('#%d %s', $this->getId(), $this->getTitle());
    }

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setThumbnailFile(File|UploadedFile|null $thumbnailFile): static
    {
        $this->thumbnailFile = $thumbnailFile;

        if (null !== $thumbnailFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getThumbnailFile(): ?File
    {
        return $this->thumbnailFile;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getThumbnailPath(): string
    {
        return '/images/recipe/'.$this->thumbnail;
    }

    public function getThumbnailPlaceholder(string $size = 'default'): string
    {
        if ('small' == $size) {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAV4AAAFeCAMAAAD69YcoAAAAyVBMVEUAAADd3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d0jcjdZAAAAQnRSTlMAAQIEBQYLDA8SExQWFxkaHyAhJCUmKi06O0lRVVZfYmNkb3V3g46PkZKbo6ivtLW5vL7FyMrT19na3Ojr7fHz9/luoWfVAAACeUlEQVR42u3cBXKDQBiAUaDukkrqSb1N3d3uf6jeoJbuBtj3nWD3zTDAvwxZJkmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJElSjD5qWxsvXrx48eLFixcvXrx48eLFixcvXrwl571oV7cK8JZiTTXaCl68eG0FrzXhxYsXL168ePHixYsXr62kwXuz/2WbeLvayjdd4sWLFy9evHjx4sWLFy9evHjx4sX7B96ir7zl1efdL/H30Qt48eLFixcvXrx48eLFixcvXrx48eKtF+9Yo7wNOK1wWoEXL168ePHixYsXL168ePHixYsXL94f8/7vOL3RFYjTCrx48eLFixcvXrx48eLFixcvXrx48XbBm/wHqGF5jdPx4sWLFy9evHjx4sWLFy9evHjx4sVrnI4XL168ePHixYsXL168ePHixYsXL168eI3T8eLFixcvXrx48eLFixcvXrx48eLFW07e3/1gYAJvyHF6By9evHjx4sWLFy9evHjx4sWLFy9evPXmHWv8Z/3mvdUNL168ePHixYsXbyV5L/CG5D3CG5L3AG9I3hbekLxNvCF5R/AG5H3N8Abk3YmzprePNJuOw/uQpu59pCuqkyZvMxLvepK6d3kk3pkkeedi3W7zpwR1d+M9zqykp3tbxOMtHlPTfRmO+TS+lJju+2Tct529pHSfJ+LqZsV1QrpXQ9Ff1gdOk9HdyrP45dtp4J6P92jaNHVWf9zj2R6O8+YPa217sjLY43lpMdVc3WjXrtba8uJokUmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmS1Js+AbSytGbYpVXAAAAAAElFTkSuQmCC';
        } else {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABgAAAAYACAMAAACHMHNZAAABVlBMVEUAAADd3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d18zGJfAAAAcXRSTlMAAQIDBAUHCAkLDA0PEBESExQWFxocHiAjJCUmKywtLzA0NTY3ODk6Pj9AQUJHSU1OT1BYWV9jZGZoaWtsbW90dXd7fH5/goOFjpSXmJqeoKOlpqq1t7nBw8XKzs/T1dfZ3N7g4uTm6+3v8fP19/n7/XM0K4AAABCKSURBVHja7d1bbxR1HMfhBSe1EKkYUEAjkaioCWjwUmO4IF544ZXR97GvZN+FhlTlwqChRoyHiASCLaek0FLLSqEFSkuB3e62XTmESqHF0m6Znfk9zz1Rvo799N/OzBYKAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAECrWxV9gKJrAFJTMkGqVpsAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQCg2RITLE/JBARWNIETAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAA9KTACkpZjyP7/kBACAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACACAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACACAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAEBzJSZIV9EEpKhkAicAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAAmikxQbpKJgCcAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABABAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQAQAAAEAAABAEAAABAAAPIiMUG6iiZYlpL9U90PJwAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEAQAAAEAAABAAAAQBAAAAQAADmSExAZCUT4AQAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgBA8yQmILJi8L9/ySXgBACAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACACAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACACAAAAgAAAIAAACAIAAACAAAAgAAAIAgAAAIAAACAAAAgCAAAAgAAAIAAACAEAzJSYgspIJcAIAQAAAEAAABAAAAQBAAAAQAAAEAAABAEAAABAAAAQAAAEA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';
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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Provides the overall average rating for this ad.
     */
    public function getAvgRatings(): float
    {
        // Calculate sum of ratings
        $sum = array_reduce($this->comments->toArray(), function ($total, $comment) {
            return $total + $comment->getRating();
        }, 0);

        // Divide to get the averages
        if (\count($this->comments) > 0) {
            return $sum / \count($this->comments);
        }

        return 0;
    }

    /**
     * Returns a user's comment on an article.
     */
    public function getCommentFromAuthor(User $author): ?Comment
    {
        /** @var Comment $comment */
        foreach ($this->comments as $comment) {
            if ($comment->getAuthor() === $author) {
                return $comment;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setRecipe($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getRecipe() === $this) {
                $comment->setRecipe(null);
            }
        }

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
}
