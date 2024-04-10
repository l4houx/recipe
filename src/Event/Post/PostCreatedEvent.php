<?php

namespace App\Event\Post;

use App\Entity\Post;

class PostCreatedEvent
{
    public function __construct(private readonly Post $post)
    {
    }

    public function getPost(): Post
    {
        return $this->post;
    }
}
