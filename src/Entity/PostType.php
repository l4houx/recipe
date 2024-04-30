<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostTypeRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasDeletedAtTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\HasGedmoTimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Traits\HasIdGedmoNameSlugAssertTrait;

#[ORM\Entity(repositoryClass: PostTypeRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class PostType
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    /**
     * @var collection<int, Post>
     */
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Post::class)]
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
            $post->setType($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getType() === $this) {
                $post->setType(null);
            }
        }

        return $this;
    }
}
