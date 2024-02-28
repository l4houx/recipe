<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;
use App\Entity\Traits\HasViewsTrait;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasTimestampTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\HasIdTitleSlugAssertTrait;
use App\Entity\Traits\HasLimit;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PostRepository::class)]
//#[Vich\Uploadable]
#[UniqueEntity('title')]
#[UniqueEntity('slug')]
class Post
{
    use HasIdTitleSlugAssertTrait;
    use HasContentTrait;
    use HasViewsTrait;
    use HasTimestampTrait;

    public const POST_LIMIT = HasLimit::POST_LIMIT;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $readtime = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $featuredImage = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToMany(targetEntity: PostCategory::class, inversedBy: 'posts')]
    private Collection $postcategories;

    #[ORM\ManyToMany(targetEntity: Keyword::class, inversedBy: 'posts')]
    private Collection $keywords;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['publishedAt' => 'DESC'])]
    private Collection $comments;

    public function __toString(): string
    {
        return sprintf('#%d %s', $this->getId(), $this->getTitle());
    }

    public function __construct()
    {
        $this->postcategories = new ArrayCollection();
        $this->keywords = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getReadtime(): ?int
    {
        return $this->readtime;
    }

    public function setReadtime(?int $readtime): static
    {
        $this->readtime = $readtime;

        return $this;
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(string $featuredImage): static
    {
        $this->featuredImage = $featuredImage;

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
     * @return Collection<int, PostCategory>
     */
    public function getPostcategories(): Collection
    {
        return $this->postcategories;
    }

    public function addPostcategory(PostCategory $postcategory): static
    {
        if (!$this->postcategories->contains($postcategory)) {
            $this->postcategories->add($postcategory);
        }

        return $this;
    }

    public function removePostcategory(PostCategory $postcategory): static
    {
        $this->postcategories->removeElement($postcategory);

        return $this;
    }

    /**
     * @return Collection<int, Keyword>
     */
    public function getKeywords(): Collection
    {
        return $this->keywords;
    }

    public function addKeyword(Keyword $keyword): static
    {
        if (!$this->keywords->contains($keyword)) {
            $this->keywords->add($keyword);
        }

        return $this;
    }

    public function removeKeyword(Keyword $keyword): static
    {
        $this->keywords->removeElement($keyword);

        return $this;
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
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }
}
