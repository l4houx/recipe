<?php

namespace App\Event\Post;

use App\Entity\Revise;

class ReviseSubmittedEvent
{
    public function __construct(private readonly Revise $revise)
    {
    }

    public function getRevise(): Revise
    {
        return $this->revise;
    }
}
