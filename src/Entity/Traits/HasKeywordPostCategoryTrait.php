<?php

namespace App\Entity\Traits;

trait HasKeywordPostCategoryTrait
{
    use HasIdNameSlugAssertTrait;
    //use HasIdGedmoNameSlugAssertTrait;
    use HasBackgroundColorTrait;
    //use HasIsOnlineTrait;
    use HasTimestampTrait;
    //use HasGedmoTimestampTrait;

    public function __toString(): string
    {
        return $this->getName();
    }
}
