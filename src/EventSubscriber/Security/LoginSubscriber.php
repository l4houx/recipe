<?php

namespace App\EventSubscriber\Security;

use App\Entity\User;
use App\Event\Password\BadPasswordLoginEvent;
use App\Service\LoginAttemptsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoginAttemptsService $service, 
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BadPasswordLoginEvent::class => 'onAuthenticationFailure',
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
        ];
    }

    public function onAuthenticationFailure(BadPasswordLoginEvent $event): void
    {
        $this->service->addAttempt($event->getUser());
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $event->getRequest()->getClientIp();

        if ($user instanceof User) {
            $ip = $event->getRequest()->getClientIp();

            if ($ip !== $user->getLastLoginIp()) {
                $user->setLastLoginIp($ip);
            }

            $user->setLastLogin(new \DateTimeImmutable());
            $this->em->flush();
        }
    }
}
