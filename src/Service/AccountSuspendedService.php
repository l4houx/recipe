<?php

namespace App\Service;

use App\Entity\User;
use App\Events\Account\AccountSuspendedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class AccountSuspendedService
{
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function suspended(User $user): void
    {
        $user->setSuspended(true);
        $this->dispatcher->dispatch(new AccountSuspendedEvent($user));
    }
}
