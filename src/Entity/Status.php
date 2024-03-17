<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\StatusRepository;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Traits\HasIsCloseTrait;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\HasIdNameColorTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
)]
class Status
{
    use HasIdNameColorTrait;
    use HasIsCloseTrait;

    /**
     * @var Collection<int,Ticket>
     */
    #[ORM\OneToMany(mappedBy: 'status', targetEntity: Ticket::class)]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setStatus($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getStatus() === $this) {
                $ticket->setStatus(null);
            }
        }

        return $this;
    }
}
