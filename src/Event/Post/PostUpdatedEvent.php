<?php

namespace App\Event\Post;

use App\Entity\Post;

class PostUpdatedEvent
{
    public function __construct(
        private readonly Post $post,
        private readonly Post $previous
    ) {
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getPrevious(): Post
    {
        return $this->previous;
    }
}
