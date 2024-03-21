<?php

namespace App\Entity\Traits;

use App\Validator\BanWord;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait HasProfileDetailsTrait
{
    // use HasAvatarVichTrait;
    use HasIsTeamTrait;
    use HasIdentifyTrait;
    use HasSocialMediaTrait;
    // use HasKnpUOAuthLoggableTrait;
    use HasRegistrationDetailsTrait;
    //use HasTimestampTrait;
    //use HasGedmoTimestampTrait;

    //#[ORM\Column(type: Types::STRING, length: 255)]
    //#[Assert\NotBlank()]
    //private string $avatar = '';

    #[ORM\Column(type: Types::STRING, length: 2, nullable: true, options: ['default' => 'FR'])]
    private ?string $country = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => null], nullable: true)]
    private ?string $theme = null;

    #[ORM\Column(type: Types::STRING, length: 2, options: ['default' => 'fr'])]
    private string $locale = 'fr';

    #[Assert\NotBlank(message: "Please don't leave your username blank!")]
    #[Assert\Length(
        min: 4,
        max: 30,
        minMessage: 'The username is too short ({{ limit }} characters minimum)',
        maxMessage: 'The username is too long ({ limit } characters maximum)'
    )]
    #[BanWord()]
    #[ORM\Column(type: Types::STRING, length: 30, unique: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private string $username = '';

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(
        min: 4,
        max: 30,
        minMessage: 'The slug is too short ({{ limit }} characters minimum)',
        maxMessage: 'The slug is too long ({ limit } characters maximum)'
    )]
    #[Assert\Regex(
        pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
        message: 'Invalid slug.',
    )]
    //#[Gedmo\Slug(fields: ['username'], unique: true, updatable: true)]
    private string $slug = '';

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\NotNull]
    #[Assert\Length(min: 5, max: 180)]
    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    #[Groups(['user:read', 'user:create', 'user:update'])]
    private string $email = '';

    /*
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->avatar = 'https://api.dicebear.com/8.x/initials/svg?seed=' . $this->username;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->avatar = 'https://api.dicebear.com/8.x/initials/svg?seed=' . $this->username;
        $this->updatedAt = new \DateTimeImmutable();
    }
    */

    public function __toString(): string
    {
        return (string) $this->getFullName();
    }

    /*
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }
    */

    public function getCountry(): string
    {
        return $this->country ?: 'FR';
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
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
