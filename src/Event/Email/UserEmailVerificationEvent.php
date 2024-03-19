<?php

namespace App\Event\Email;

use App\Entity\UserEmailVerification;

class UserEmailVerificationEvent
{
    public function __construct(
        public UserEmailVerification $userEmailVerification
    ) {
    }
}
