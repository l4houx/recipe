<?php

namespace App\Event\Notification;

use App\Entity\User;

class NotificationReadEvent
{
    public function __construct(private readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
