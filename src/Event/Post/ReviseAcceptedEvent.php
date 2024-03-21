<?php

namespace App\Event\Post;

use App\Entity\Revise;

class ReviseAcceptedEvent
{
    public function __construct(private readonly Revise $revise)
    {
    }

    public function getRevise(): Revise
    {
        return $this->revise;
    }
}
