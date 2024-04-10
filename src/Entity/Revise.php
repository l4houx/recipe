<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasIdTrait;
use App\Repository\ReviseRepository;
use App\Validator\NotTheSameContent;
use App\Entity\Traits\HasGedmoTimestampTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviseRepository::class)]
#[NotTheSameContent]
class Revise
{
    use HasIdTrait;
    use HasGedmoTimestampTrait;

    final public const PENDING = 0;
    final public const ACCEPTED = 1;
    final public const REJECTED = -1;

    #[ORM\Column(type: Types::INTEGER, length: 1, options: ['default' => 0])]
    private int $status = self::PENDING;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $content = '';

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Post $target;

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getTarget(): Post
    {
        return $this->target;
    }

    public function setTarget(Post $target): static
    {
        $this->target = $target;

        return $this;
    }
}
