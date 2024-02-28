<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait HasProfileDetailsTrait
{
    // use HasAvatarVichTrait;
    use HasIsTeamTrait;
    // use HasIdentifyTrait;
    // use HasEmailTrait;
    // use HasSocialMediaTrait;
    // use HasKnpUOAuthLoggableTrait;
    use HasRegistrationDetailsTrait;

    #[Assert\NotBlank(message: "Please don't leave your username blank!")]
    #[Assert\Length(
        min: 4,
        max: 30,
        minMessage: 'The username is too short ({{ limit }} characters minimum)',
        maxMessage: 'The username is too long ({ limit } characters maximum)'
    )]
    #[ORM\Column(type: Types::STRING, length: 30, unique: true)]
    private string $username = '';

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(
        min: 4,
        max: 30,
        minMessage: 'The username is too short ({{ limit }} characters minimum)',
        maxMessage: 'The username is too long ({ limit } characters maximum)'
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Invalid slug.',
    )]
    private string $slug = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\NotNull]
    #[Assert\Length(min: 5, max: 180)]
    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    private string $email = '';

    public function __toString(): string
    {
        return (string) $this->getFullName();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = trim($username ?: '');

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email ?: '';

        return $this;
    }
}