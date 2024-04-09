<?php

namespace App\Entity\Traits;

trait HasKeywordPostCategoryTrait
{
    use HasIdGedmoNameSlugAssertTrait;
    use HasBackgroundColorTrait;
    use HasIsOnlineTrait;
    use HasGedmoTimestampTrait;
    use HasDeletedAtTrait;

    public function __toString(): string
    {
        return $this->getName();
    }
}
