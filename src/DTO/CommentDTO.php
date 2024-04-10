<?php

namespace App\DTO;

use App\Entity\Comment;

abstract class CommentDTO
{
    public ?int $id = null;

    public ?string $email = null;

    public ?string $username = null;

    public string $content = '';

    public ?int $userId = null;

    public ?int $target = null;

    public ?int $parent = 0;

    public ?Comment $entity = null;

    public int $publishedAt = 0;
}
