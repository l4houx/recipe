<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class SearchDataDTO
{
    /** @var int */
    public $page = 1;

    public string $keywords = '';

    public array $categories = [];
}
