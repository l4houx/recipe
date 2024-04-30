<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasStatusTrait
{
    final public const PENDING = 0;
    final public const ACCEPTED = 1;
    final public const REJECTED = -1;

    #[ORM\Column(type: Types::INTEGER, length: 1, options: ['default' => 0])]
    private int $status = self::PENDING;

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }
}
