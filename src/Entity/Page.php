<?php

namespace App\Entity;

use App\Entity\Traits\HasLimit;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PageRepository;
use App\Entity\Traits\HasViewsTrait;
use App\Entity\Traits\HasContentTrait;
use App\Entity\Traits\HasTimestampTrait;
use App\Entity\Traits\HasGedmoTimestampTrait;
use App\Entity\Traits\HasIdTitleSlugAssertTrait;
use App\Entity\Traits\HasIdGedmoTitleSlugAssertTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[UniqueEntity('title')]
#[UniqueEntity('slug')]
class Page
{
    use HasIdTitleSlugAssertTrait;
    //use HasIdGedmoTitleSlugAssertTrait;
    use HasContentTrait;
    use HasViewsTrait;
    use HasTimestampTrait;
    //use HasGedmoTimestampTrait;

    public const PAGE_LIMIT = HasLimit::PAGE_LIMIT;

    public function __toString(): string
    {
        return (string) $this->getTitle() ?: '';
    }
}
