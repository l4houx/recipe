<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class AccountUpdatedSocialDTO
{
    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    public ?string $externallink = null;

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
    public ?string $youtubeurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    public ?string $twitterurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    public ?string $instagramurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    public ?string $facebookurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    public ?string $googleplusurl = null;

    #[Assert\Length(max: 255)]
    #[Assert\Url(
        message: "This value is not a valid URL.", 
        protocols: ['http', 'https'],
    )]
    public ?string $linkedinurl = null;

    public User $user;

    public function __construct(User $user)
    {
        // External link
        $this->externallink = $user->getExternallink();
        // Social Media
        $this->youtubeurl = $user->getYoutubeurl();
        $this->twitterurl = $user->getTwitterUrl();
        $this->instagramurl = $user->getInstagramUrl();
        $this->facebookurl = $user->getFacebookUrl();
        $this->googleplusurl = $user->getGoogleplusUrl();
        $this->linkedinurl = $user->getLinkedinUrl();
        // User
        $this->user = $user;
    }

    public function getId(): int
    {
        return $this->user->getId() ?: 0;
    }
}
