<?php

namespace App\Event\Account;

use App\Entity\User;

class AccountUserCreatedEvent
{
    public function __construct(
        private readonly User $user,
        private readonly bool $usingOauth = false
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isUsingOauth(): bool
    {
        return $this->usingOauth;
    }
}
