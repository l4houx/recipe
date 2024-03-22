<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

const EASY = 0;
const MEDIUM = 1;
const HARD = 2;

trait HasLevelTrait
{
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    private int $level = 0;

    public static array $levels = [
        EASY => 'Beginner',
        MEDIUM => 'Intermediate',
        HARD => 'Advance',
    ];

    public static array $colors = [
        EASY => 'success',
        MEDIUM => 'primary',
        HARD => 'danger',
    ];

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getLevelName(): string
    {
        return self::$levels[$this->level];
    }

    public function getLevelColor(): string
    {
        return self::$colors[$this->level];
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }
}
