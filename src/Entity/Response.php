<?php

namespace App\Entity;

use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\HasContentTrait;
use App\Repository\ResponseRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\HasDeletedAtTrait;
use App\Entity\Traits\HasIdApiIdAuthorTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;

#[ORM\Entity(repositoryClass: ResponseRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post(),
        new \ApiPlatform\Metadata\GetCollection(),
    ],
)]
class Response
{
    use HasIdApiIdAuthorTrait;
    use HasContentTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    public const RESPONSE_LIMIT = HasLimit::RESPONSE_LIMIT;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'responses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ticket $ticket = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }
}
