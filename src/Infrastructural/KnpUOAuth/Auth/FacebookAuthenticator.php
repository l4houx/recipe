<?php

namespace App\Infrastructural\KnpUOAuth\Auth;

use App\Entity\User;
use App\Infrastructural\KnpUOAuth\Exception\EmailAlreadyUsedException;
use App\Repository\UserRepository;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FacebookAuthenticator extends AbstractOAuth2Authenticator
{
    protected string $serviceName = 'facebook';

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getUserFromResourceOwner(ResourceOwnerInterface $facebookUser, UserRepository $userRepository): ?User
    {
        if (!($facebookUser instanceof FacebookUser)) {
            throw new \RuntimeException($this->translator->trans('Expecting FacebookClient as the first parameter'));
        }

        $user = $userRepository->findForOauth('facebook', $facebookUser->getId(), $facebookUser->getEmail());

        if ($user && null === $user->getFacebookId()) {
            throw new EmailAlreadyUsedException();
        }

        return $user;
    }
}
