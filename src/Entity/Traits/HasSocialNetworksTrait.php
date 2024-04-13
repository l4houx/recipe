<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\String\u;

trait HasSocialNetworksTrait
{
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    private ?string $externallink = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    private ?string $website = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Assert\Length(min: 1, max: 50)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    #[Assert\Regex(
        pattern: '^(http|https):\/\/(www\.youtube\.com|www\.dailymotion\.com)\/?',
        match: true,
        message: "The URL must match the URL of a Youtube or DailyMotion video",
    )]
    private ?string $youtubeurl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    private ?string $twitterurl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    private ?string $instagramurl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    private ?string $facebookurl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    private ?string $googleplusurl = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    private ?string $linkedinurl = null;

    public function getExternallink(): ?string
    {
        return u($this->externallink)->upper()->toString();
    }

    public function setExternallink(?string $externallink): static
    {
        $this->externallink = $externallink;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return u($this->website)->upper()->toString();
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

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
