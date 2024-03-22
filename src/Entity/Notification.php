<?php

namespace App\Entity;

use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    use HasGedmoTimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Groups(['read:notification', 'create:notification'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['create:notification'])]
    private ?string $channel = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $target = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['read:notification', 'create:notification'])]
    private string $message;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', onDelete: 'CASCADE', nullable: true)]
    private ?User $user = null;

    public function getId(): int
    {
        return $this->id ?: $this->getCreatedAt()->getTimestamp();
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    public function setChannel(?string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isRead(): bool
    {
        if (null === $this->user) {
            return false;
        }

        $notificationsReadAt = $this->user->getNotificationsReadAt();

        return $notificationsReadAt ? ($this->createdAt > $notificationsReadAt) : false;
    }
}
