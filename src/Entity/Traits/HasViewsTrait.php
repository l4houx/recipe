<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait HasViewsTrait
{
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $views = 0;

    public function __construct()
    {
        $this->views = 0;
    }

    public function viewed(): void
    {
        ++$this->views;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;

        return $this;
    }
}
