<?php

namespace App\Event\Subscription;

use App\Entity\User;

class PremiumSubscriptionEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
