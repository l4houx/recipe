<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TicketRepository;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\HasContentTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\HasDeletedAtTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\HasIdApiIdAuthorTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
#[ApiResource(
    operations: [
        new \ApiPlatform\Metadata\Get(),
        new \ApiPlatform\Metadata\Post(),
        new \ApiPlatform\Metadata\GetCollection(),
    ],
)]
class Ticket
{
    use HasIdApiIdAuthorTrait;
    use HasContentTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    public const TICKET_LIMIT = HasLimit::TICKET_LIMIT;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $subject = '';

    #[ORM\ManyToOne()]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?Application $application = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    /**
     * @var Collection<int,Response>
     */
    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: Response::class, orphanRemoval: true)]
    private Collection $responses;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

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

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, Response>
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Response $response): static
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->setTicket($this);
        }

        return $this;
    }

    public function removeResponse(Response $response): static
    {
        if ($this->responses->removeElement($response)) {
            // set the owning side to null (unless already changed)
            if ($response->getTicket() === $this) {
                $response->setTicket(null);
            }
        }

        return $this;
    }
}
