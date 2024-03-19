<?php

namespace App\Event\Account;

use App\Entity\User;

class AccountDeletedEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
