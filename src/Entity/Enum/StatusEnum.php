<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum StatusEnum: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Rejected = 'rejected';
}
