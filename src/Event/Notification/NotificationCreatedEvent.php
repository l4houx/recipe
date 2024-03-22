<?php

namespace App\Event\Notification;

use App\Entity\Notification;

class NotificationCreatedEvent
{
    public function __construct(private readonly Notification $notification)
    {
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }
}
