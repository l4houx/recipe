<?php

namespace App\Entity\Setting;

use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdGedmoTitleSlugAssertTrait;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasViewsTrait;
use App\Repository\Setting\PageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[UniqueEntity('title')]
#[UniqueEntity('slug')]
class Page
{
    use HasIdGedmoTitleSlugAssertTrait;
    use HasContentTrait;
    use HasViewsTrait;
    use HasGedmoTimestampTrait;

    public const PAGE_LIMIT = HasLimit::PAGE_LIMIT;

    public function __toString(): string
    {
        return (string) $this->getTitle() ?: '';
    }
}
