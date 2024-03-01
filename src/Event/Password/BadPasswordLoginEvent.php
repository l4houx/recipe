<?php

namespace App\Event\Password;

use App\Entity\User;

class BadPasswordLoginEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
