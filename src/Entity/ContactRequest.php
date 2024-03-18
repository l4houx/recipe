<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\HasIdTrait;
use geertw\IpAnonymizer\IpAnonymizer;
use App\Repository\ContactRequestRepository;

#[ORM\Entity(repositoryClass: ContactRequestRepository::class)]
class ContactRequest
{
    use HasIdTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $ip = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function setRawIp(?string $ip): static
    {
        $this->ip = (new IpAnonymizer())->anonymize($ip);

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
