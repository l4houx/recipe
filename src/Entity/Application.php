<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIsOnlineTrait;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['application:read']],
)]
class Application implements UserInterface
{
    // use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;

    public const APP_LIMIT = HasLimit::APP_LIMIT;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['application:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['application:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    private ?string $token = null;

    /**
     * @var collection<int, Ticket>
     */
    #[ORM\OneToMany(mappedBy: 'application', targetEntity: Ticket::class)]
    #[Groups(['application:read'])]
    private Collection $tickets;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['application:read'])]
    private ?User $user = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['application:read'])]
    private array $roles = [];

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->token;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = HasRoles::ADMINAPPLICATION;

        return array_values(array_unique($roles));
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
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
            $ticket->setApplication($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getApplication() === $this) {
                $ticket->setApplication(null);
            }
        }

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
}
