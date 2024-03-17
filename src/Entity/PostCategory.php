<?php

namespace App\Entity;

use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostCategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Traits\HasKeywordPostCategoryTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PostCategoryRepository::class)]
#[UniqueEntity('name')]
#[UniqueEntity('slug')]
class PostCategory
{
    use HasKeywordPostCategoryTrait;

    public const POSTCATEGORY_LIMIT = HasLimit::POSTCATEGORY_LIMIT;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'postcategories')]
    private ?self $parent = null;

    /**
     * @var collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $postcategories;

    /**
     * @var collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'postcategories')]
    private Collection $posts;

    public function __construct()
    {
        $this->postcategories = new ArrayCollection();
        $this->posts = new ArrayCollection();
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getPostcategories(): Collection
    {
        return $this->postcategories;
    }

    public function addPostcategory(self $postcategory): static
    {
        if (!$this->postcategories->contains($postcategory)) {
            $this->postcategories->add($postcategory);
            $postcategory->setParent($this);
        }

        return $this;
    }

    public function removePostcategory(self $postcategory): static
    {
        if ($this->postcategories->removeElement($postcategory)) {
            // set the owning side to null (unless already changed)
            if ($postcategory->getParent() === $this) {
                $postcategory->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->addPostcategory($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            $post->removePostcategory($this);
        }

        return $this;
    }
}
