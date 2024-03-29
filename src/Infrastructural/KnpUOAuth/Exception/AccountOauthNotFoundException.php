<?php

namespace App\Infrastructural\KnpUOAuth\Exception;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Error returned when no user matching the OAUTH response can be found.
 */
class AccountOauthNotFoundException extends AuthenticationException
{
    public function __construct(private readonly ResourceOwnerInterface $resourceOwner)
    {
    }

    public function getResourceOwner(): ResourceOwnerInterface
    {
        return $this->resourceOwner;
    }
}
