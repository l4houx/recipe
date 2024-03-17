<?php

namespace App\DTO;

class CategoryWithCountDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $count,
        public readonly string $color,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt
    ) {
        # code...
    }
}
