<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\File;
use App\Infrastructural\Payment\Validator\StripePlan;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PricingDTO
{
    public ?UploadedFile $thumbnailFile = null;

    #[Assert\NotBlank]
    public string $title = '';

    #[Assert\NotBlank]
    public string $subtitle = '';

    #[Assert\NotBlank]
    public string $btn = '';

    #[Assert\NotBlank]
    public string $monthly = '';

    #[Assert\NotBlank]
    #[Assert\GreaterThan(value: 1)]
    public float $price = 0;

    #[Assert\Positive]
    public int $duration = 1;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50)]
    public string $symbol = '';

    #[Assert\NotBlank]
    #[StripePlan]
    public ?string $stripeId = null;
}
