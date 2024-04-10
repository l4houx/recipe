<?php

namespace App\Entity;

use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasIdTrait;
use App\Entity\Traits\HasIPTrait;
use App\Entity\Traits\HasIsApprovedTrait;
use App\Entity\Traits\HasIsRGPDTrait;
use App\Entity\Traits\HasLimit;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    use HasIdTrait;
    use HasIPTrait;
    use HasIsApprovedTrait;
    use HasIsRGPDTrait;
    use HasContentTrait;

    public const COMMENT_LIMIT = HasLimit::COMMENT_LIMIT;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    //#[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: true)]
    private ?User $author = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false, name: 'post_id')]
    private Post $target;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $replies;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $publishedAt;

    #[Assert\IsTrue(message: 'The content of this comment is considered spam.')]
    public function isLegitComment(): bool
    {
        $containsInvalidCharacters = null !== u($this->content)->indexOf('@');

        return !$containsInvalidCharacters;
    }

    public function __toString(): string
    {
        return "{$this->author->getUsername()} {$this->publishedAt->format('d/m/y à H:i:s')}";
    }

    public function __construct()
    {
        $this->publishedAt = new \DateTimeImmutable();
        $this->replies = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getUsername(): string
    {
        if (null !== $this->author) {
            return $this->author->getUsername();
        }

        return $this->username ?: '';
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

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

    public function getTarget(): ?Post
    {
        return $this->target;
    }

    public function setTarget(?Post $target): static
    {
        $this->target = $target;

        return $this;
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
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $comment): static
    {
        if (!$this->replies->contains($comment)) {
            $this->replies->add($comment);
            $comment->setParent($this);
        }

        return $this;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
