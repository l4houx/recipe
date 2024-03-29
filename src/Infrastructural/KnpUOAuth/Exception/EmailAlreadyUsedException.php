<?php

namespace App\Infrastructural\KnpUOAuth\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class EmailAlreadyUsedException extends CustomUserMessageAuthenticationException
{
    public function __construct(
        string $message = 'An account already exists with this email. To link your facebook account to this account, log in and go to your profile.',
        array $messageData = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $messageData, $code, $previous);
    }
}
