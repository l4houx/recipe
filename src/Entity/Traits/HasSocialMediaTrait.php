<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

trait HasSocialMediaTrait
{
    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    #[Assert\Length(max: 5)]
    private ?string $year = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => '#'])]
    private ?string $externallink = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => '#'])]
    private ?string $youtubeurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => '#'])]
    private ?string $twitterurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => '#'])]
    private ?string $instagramurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => '#'])]
    private ?string $facebookurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => '#'])]
    private ?string $googleplusurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => '#'])]
    private ?string $linkedinurl = null;

    public function hasSocialMedia(): bool
    {
        return $this->externallink
            || $this->youtubeurl || $this->twitterurl
            || $this->instagramurl || $this->facebookurl
            || $this->googleplusurl || $this->linkedinurl;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(?string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getExternallink(): ?string
    {
        return u($this->externallink)->upper()->toString();
    }

    public function setExternallink(?string $externallink): static
    {
        $this->externallink = $externallink;

        return $this;
    }

    public function getYoutubeurl(): ?string
    {
        return u($this->youtubeurl)->upper()->toString();
    }

    public function setYoutubeurl(?string $youtubeurl): static
    {
        $this->youtubeurl = $youtubeurl;

        return $this;
    }

    public function getTwitterUrl(): ?string
    {
        return u($this->twitterurl)->upper()->toString();
    }

    public function setTwitterUrl(?string $twitterurl): static
    {
        $this->twitterurl = $twitterurl;

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return u($this->instagramurl)->upper()->toString();
    }

    public function setInstagramUrl(?string $instagramurl): static
    {
        $this->instagramurl = $instagramurl;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return u($this->facebookurl)->upper()->toString();
    }

    public function setFacebookUrl(?string $facebookurl): static
    {
        $this->facebookurl = $facebookurl;

        return $this;
    }

    public function getGoogleplusUrl(): ?string
    {
        return u($this->googleplusurl)->upper()->toString();
    }

    public function setGoogleplusUrl(?string $googleplusurl): static
    {
        $this->googleplusurl = $googleplusurl;

        return $this;
    }

    public function getLinkedinUrl(): ?string
    {
        return u($this->linkedinurl)->upper()->toString();
    }

    public function setLinkedinUrl(?string $linkedinurl): static
    {
        $this->linkedinurl = $linkedinurl;

        return $this;
    }
}
