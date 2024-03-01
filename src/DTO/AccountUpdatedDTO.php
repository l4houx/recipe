<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints as Assert;

/** Data for updating the user profile. */
#[Unique(field: 'email', entityClass: User::class)]
#[Unique(field: 'username', entityClass: User::class)]
class AccountUpdatedDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 180)]
    #[Assert\NotNull]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank(normalizer: 'trim')]
    #[Assert\NotNull]
    #[Assert\Length(min: 4, max: 30)]
    public string $username = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 20)]
    public string $firstname = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 20)]
    public string $lastname = '';

    #[Assert\NotBlank]
    #[Assert\Country]
    public ?string $country = 'FR';

    public User $user;

    public function __construct(User $user)
    {
        // Contact
        $this->email = $user->getEmail();
        // Profile
        $this->firstname = $user->getFirstname();
        $this->lastname = $user->getLastname();
        $this->username = $user->getUsername();
        // Pays
        $this->country = $user->getCountry();
        // User
        $this->user = $user;
    }

    public function getId(): int
    {
        return $this->user->getId() ?: 0;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username ?: '';

        return $this;
    }
}
