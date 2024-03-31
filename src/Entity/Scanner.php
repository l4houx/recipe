<?php

namespace App\Entity;

use App\Entity\Traits\HasIdTrait;
use App\Repository\ScannerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScannerRepository::class)]
class Scanner
{
    use HasIdTrait;

    #[ORM\Column(type: Types::STRING, length: 25)]
    #[Assert\NotBlank(groups: ['create', 'update'])]
    #[Assert\Length(min: 2, max: 25, groups: ['create', 'update'])]
    private string $name = '';

    #[ORM\ManyToOne(inversedBy: 'scanners')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

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
