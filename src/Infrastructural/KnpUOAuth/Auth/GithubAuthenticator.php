<?php

namespace App\Infrastructural\KnpUOAuth\Auth;

use App\Entity\User;
use App\Infrastructural\KnpUOAuth\Exception\NotVerifiedEmailException;
use App\Repository\UserRepository;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Translation\TranslatorInterface;

class GithubAuthenticator extends AbstractOAuth2Authenticator
{
    protected string $serviceName = 'github';

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getUserFromResourceOwner(ResourceOwnerInterface $githubUser, UserRepository $userRepository): ?User
    {
        if (!($githubUser instanceof GithubResourceOwner)) {
            throw new \RuntimeException($this->translator->trans('Expecting GithubResourceOwner as the first parameter'));
        }

        $user = $userRepository->findForOauth('github', $githubUser->getId(), $githubUser->getEmail());

        if ($user && null === $user->getGithubId()) {
            $user->setGithubId($githubUser->getId());
            $this->em->flush();
        }

        return $user;
    }

    public function getResourceOwnerFromCredentials(AccessToken $credentials): GithubResourceOwner
    {
        /** @var GithubResourceOwner $githubUser */
        $githubUser = parent::getResourceOwnerFromCredentials($credentials);

        $response = HttpClient::create()->request(
            'GET',
            'https://api.github.com/user/emails',
            [
                'headers' => [
                    'authorization' => "token {$credentials->getToken()}",
                ],
            ]
        );

        $emails = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        foreach ($emails as $email) {
            if (true === $email['primary'] && true === $email['verified']) {
                $data = $githubUser->toArray();
                $data['email'] = $email['email'];

                return new GithubResourceOwner($data);
            }
        }

        throw new NotVerifiedEmailException();
    }
}
