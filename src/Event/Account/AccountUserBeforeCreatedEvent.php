<?php

namespace App\Event\Account;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class AccountUserBeforeCreatedEvent
{
    public function __construct(
        public readonly User $user,
        public readonly Request $request
    ) {
    }
}
