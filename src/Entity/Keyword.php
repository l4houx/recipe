<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\KeywordRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Traits\HasKeywordPostCategoryTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: KeywordRepository::class)]
#[UniqueEntity('name')]
#[UniqueEntity('slug')]
class Keyword
{
    use HasKeywordPostCategoryTrait;

    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'keywords')]
    private Collection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
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
            $post->addKeyword($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            $post->removeKeyword($this);
        }

        return $this;
    }
}
