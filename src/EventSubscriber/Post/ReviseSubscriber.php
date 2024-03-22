<?php

namespace App\EventSubscriber\Post;

use App\Entity\Revise;
use App\Event\Post\ReviseRefusedEvent;
use App\Event\Post\ReviseAcceptedEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\Content\ContentUpdatedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReviseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ReviseRefusedEvent::class => 'onReviseRefused',
            ReviseAcceptedEvent::class => 'onReviseAccepted',
        ];
    }

    public function onReviseRefused(ReviseRefusedEvent $event): void
    {
        $revision = $event->getRevise();
        $revision->setStatus(Revise::REJECTED);
        $revision->setComment($event->getComment());

        $this->em->flush();
    }

    public function onReviseAccepted(ReviseAcceptedEvent $event): void
    {
        $content = $event->getRevise()->getTarget();
        $previous = clone $content;

        $content->setContent($event->getRevise()->getContent());
        $event->getRevise()->setStatus(Revise::ACCEPTED);
        $event->getRevise()->setContent('');

        $this->em->flush();
        $this->dispatcher->dispatch(new ContentUpdatedEvent($content, $previous));
    }
}
