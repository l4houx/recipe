<?php

declare(strict_types=1);

namespace App\EventSubscriber\Notification;

use App\Event\Post\ReviseAcceptedEvent;
use App\Event\Post\ReviseRefusedEvent;
use App\Service\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReviseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly NotificationService $notificationService
    ) {
    }

    public function onReviseAccepted(ReviseAcceptedEvent $event): void
    {
        $revise = $event->getRevise();

        $this->notificationService->notifyUser(
            $revise->getAuthor(),
            sprintf(
                $this->translator->trans('Your modification for article <strong>%s</strong> has been accepted'),
                $revise->getTarget()->getTitle()
            ),
            $revise
        );
    }

    public function onReviseRefused(ReviseRefusedEvent $event): void
    {
        $revise = $event->getRevise();

        $this->notificationService->notifyUser(
            $revise->getAuthor(),
            sprintf(
                $this->translator->trans('Your edit for item <strong>%s</strong> was rejected'),
                $revise->getTarget()->getTitle()
            ),
            $revise
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ReviseAcceptedEvent::class => 'onReviseAccepted',
            ReviseRefusedEvent::class => 'onReviseRefused',
        ];
    }
}
