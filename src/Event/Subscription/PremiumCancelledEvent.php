<?php

namespace App\Event\Subscription;

use App\Entity\User;

class PremiumCancelledEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
