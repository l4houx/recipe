<?php

namespace App\Event\Post;

use App\Entity\Revise;

class ReviseRefusedEvent
{
    public function __construct(private readonly Revise $revise, private string $comment)
    {
    }

    public function getRevise(): Revise
    {
        return $this->revise;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
