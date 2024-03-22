<?php

namespace App\EventSubscriber\Security;

use App\Event\Account\AccountUserBeforeCreatedEvent;
use App\Service\RegisterDurationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RegisterSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly RegisterDurationService $registerDurationService)
    {
    }

    public function onRequest(RequestEvent $event): void
    {
        if ('register' !== $event->getRequest()->attributes->get('_route')
            || !$event->getRequest()->isMethod('GET')
        ) {
            return;
        }

        $this->registerDurationService->startTimer($event->getRequest());
    }

    public function onRegister(AccountUserBeforeCreatedEvent $event): void
    {
        $event->user->setRegisterDuration($this->registerDurationService->getDuration($event->request));
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onRequest',
            AccountUserBeforeCreatedEvent::class => 'onRegister',
        ];
    }
}
