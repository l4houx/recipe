<?php

namespace App\Event\Account;

use App\Entity\User;

class AccountDeletedEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUsers(): User
    {
        return $this->user;
    }
}