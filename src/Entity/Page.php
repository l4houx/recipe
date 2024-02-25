<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PageRepository;
use App\Entity\Traits\HasViewsTrait;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasTimestampTrait;
use App\Entity\Traits\HasIdTitleSlugAssertTrait;

#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    use HasIdTitleSlugAssertTrait;
    use HasContentTrait;
    use HasViewsTrait;
    use HasTimestampTrait;

    public function __toString(): string
    {
        return (string) $this->getTitle() ?: '';
    }
}
