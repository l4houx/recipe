<?php

namespace App\Event\Content;

use App\Entity\Content;

class ContentCreatedEvent
{
    public function __construct(private readonly Content $content)
    {
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}
