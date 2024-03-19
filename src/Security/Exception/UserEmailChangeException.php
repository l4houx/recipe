<?php

namespace App\Security\Exception;

use App\Entity\UserEmailVerification;

class UserEmailChangeException extends \Exception
{
    public function __construct(
        public UserEmailVerification $userEmailVerification
    ) {
        parent::__construct();
    }
}
