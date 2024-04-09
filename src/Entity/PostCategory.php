<?php

namespace App\Entity;

use App\Entity\Traits\HasKeywordPostCategoryTrait;
use App\Entity\Traits\HasLimit;
use App\Repository\PostCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PostCategoryRepository::class)]
#[UniqueEntity('name')]
#[UniqueEntity('slug')]
class PostCategory
{
    use HasKeywordPostCategoryTrait;

    public const POSTCATEGORY_LIMIT = HasLimit::POSTCATEGORY_LIMIT;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    private int $postsCount = 0;

    /**
     * @var collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'category')]
    private Collection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function getPostsCount(): int
    {
        return $this->postsCount;
    }

    public function setPostsCount(int $postsCount): static
    {
        $this->postsCount = $postsCount;

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
            $post->setCategory($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }

        return $this;
    }
}
