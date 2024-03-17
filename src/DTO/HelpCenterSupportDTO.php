<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class HelpCenterSupportDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 200)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 180)]
    #[Assert\Email]
    public string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 100, max: 2000)]
    public string $message = '';

    #[Assert\NotBlank]
    public string $service = '';
}
