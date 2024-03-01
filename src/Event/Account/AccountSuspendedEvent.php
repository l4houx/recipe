<?php

namespace App\Events\Account;

use App\Entity\User;

class AccountSuspendedEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
