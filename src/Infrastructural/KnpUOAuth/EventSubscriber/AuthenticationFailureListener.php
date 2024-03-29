<?php

namespace App\Infrastructural\KnpUOAuth\EventSubscriber;

use App\Infrastructural\KnpUOAuth\Exception\AccountAuthenticatedException;
use App\Infrastructural\KnpUOAuth\Exception\AccountOauthNotFoundException;
use App\Infrastructural\KnpUOAuth\Service\SocialLoginService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationFailureListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SocialLoginService $socialLoginService,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => 'onAuthenticationFailure',
        ];
    }

    public function onAuthenticationFailure(LoginFailureEvent $event): void
    {
        $exception = $event->getException();

        if ($exception instanceof AccountOauthNotFoundException) {
            $this->onUserNotFound($exception);
        }
        if ($exception instanceof AccountAuthenticatedException) {
            $this->onUserAlreadyAuthenticated($exception);
        }
    }

    public function onUserNotFound(AccountOauthNotFoundException $exception): void
    {
        $this->socialLoginService->persist($this->requestStack->getSession(), $exception->getResourceOwner());
    }

    public function onUserAlreadyAuthenticated(AccountAuthenticatedException $exception): void
    {
        $resourceOwner = $exception->getResourceOwner();
        $user = $exception->getUser();

        /** @var array{type: string} $data */
        $data = $this->normalizer->normalize($exception->getResourceOwner());

        $setter = 'set'.ucfirst($data['type']).'Id';
        $user->$setter($resourceOwner->getId());
        $this->em->flush();

        $session = $this->requestStack->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->set('success', $this->translator->trans('Your account has been successfully linked to '.$data['type']));
        }
    }
}
