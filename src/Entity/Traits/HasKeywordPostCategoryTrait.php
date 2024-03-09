<?php

namespace App\Entity\Traits;

trait HasKeywordPostCategoryTrait
{
    use HasIdNameSlugAssertTrait;
    use HasBackgroundColorTrait;
    use HasTimestampTrait;

    public function __toString(): string
    {
        return $this->getName();
    }
}
