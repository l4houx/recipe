<?php

namespace App\Infrastructural\KnpUOAuth\Auth;

use App\Entity\User;
use App\Infrastructural\KnpUOAuth\Exception\AccountAuthenticatedException;
use App\Infrastructural\KnpUOAuth\Exception\AccountOauthNotFoundException;
use App\Repository\UserRepository;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractOAuth2Authenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    protected string $serviceName = '';

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        protected EntityManagerInterface $em,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly SecurityService $securityService
    ) {
    }

    public function supports(Request $request): bool
    {
        if ('' === $this->serviceName) {
            throw new \Exception("You must set a \$serviceName property (for instance 'github', 'facebook')");
        }

        return 'oauth_check' === $request->attributes->get('_route') && $request->get('service') === $this->serviceName;
    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('login'));
    }

    public function getCredentials(Request $request): AccessTokenInterface
    {
        return $this->fetchAccessToken($this->getClient());
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->getClient();

        try {
            $accessToken = $client->getAccessToken();
        } catch (\Exception) {
            throw new CustomUserMessageAuthenticationException(sprintf($this->translator->trans('An error occurred while retrieving the access token %s'), $this->serviceName));
        }

        try {
            $resourceOwner = $this->getResourceOwnerFromCredentials($accessToken);
        } catch (\Exception) {
            throw new CustomUserMessageAuthenticationException(sprintf($this->translator->trans('An error occurred while communicating with %s'), $this->serviceName));
        }

        $user = $this->securityService->getUserOrNull();

        if ($user) {
            throw new AccountAuthenticatedException($user, $resourceOwner);
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->em->getRepository(User::class);
        $user = $this->getUserFromResourceOwner($resourceOwner, $userRepository);
        if (null === $user) {
            throw new AccountOauthNotFoundException($resourceOwner);
        }

        $userLoader = fn () => $user;

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), $userLoader),
            [
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        if ($exception instanceof AccountOauthNotFoundException) {
            return new RedirectResponse($this->router->generate('register', ['oauth' => 1]));
        }

        if ($exception instanceof AccountAuthenticatedException) {
            return new RedirectResponse($this->router->generate('dashboard_account_index'));
        }

        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->router->generate('login'));
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): RedirectResponse {
        // We force the remember me to trigger the AbstractRememberMeServices (while waiting for something better)
        $request->request->set('_remember_me', '1');

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('home'));
    }

    protected function getResourceOwnerFromCredentials(AccessToken $credentials): ResourceOwnerInterface
    {
        return $this->getClient()->fetchUserFromToken($credentials);
    }

    protected function getUserFromResourceOwner(
        ResourceOwnerInterface $resourceOwner,
        UserRepository $userRepository
    ): ?User {
        return null;
    }

    protected function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient($this->serviceName);
    }
}
