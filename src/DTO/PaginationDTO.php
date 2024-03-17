<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PaginationDTO
{
    public function __construct(
        #[Assert\Positive()]
        public readonly ?int $page = 1
    ) {
        # code...
    }
}
