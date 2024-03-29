<?php

namespace App\Infrastructural\KnpUOAuth\Auth;

use App\Entity\User;
use App\Infrastructural\KnpUOAuth\Exception\NotVerifiedEmailException;
use App\Repository\UserRepository;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleAuthenticator extends AbstractOAuth2Authenticator
{
    protected string $serviceName = 'google';

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getUserFromResourceOwner(ResourceOwnerInterface $googleUser, UserRepository $userRepository): ?User
    {
        if (!($googleUser instanceof GoogleUser)) {
            throw new \RuntimeException($this->translator->trans('Expecting GoogleUser as the first parameter'));
        }

        if (true !== ($googleUser->toArray()['email_verified'] ?? null)) {
            throw new NotVerifiedEmailException();
        }

        $user = $userRepository->findForOauth('google', $googleUser->getId(), $googleUser->getEmail());

        if ($user && null === $user->getGoogleId()) {
            $user->setGoogleId($googleUser->getId());
            $this->em->flush();
        }

        return $user;
    }
}
