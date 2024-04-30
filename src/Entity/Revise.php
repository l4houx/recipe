<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasIdTrait;
use App\Repository\ReviseRepository;
use App\Validator\NotTheSameContent;
use App\Entity\Traits\HasStatusTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviseRepository::class)]
#[NotTheSameContent]
class Revise
{
    use HasIdTrait;
    use HasStatusTrait;
    use HasGedmoTimestampTrait;

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

    public function __toString(): string
    {
        return sprintf('#%d %s', $this->getId(), $this->getTarget()->getTitle());
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
