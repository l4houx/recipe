<?php

namespace App\EventSubscriber\Security;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Report;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ReportAccountSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['setUser', EventPriorities::POST_DESERIALIZE],
        ];
    }

    public function setUser(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $report = $request->get('data');
        $method = $request->getMethod();
        $user = $this->security->getUser();

        if (
            !$report instanceof Report
            || Request::METHOD_POST !== $method
            || !$user instanceof User
        ) {
            return;
        }

        $request->attributes->set(
            'data',
            $report->setAuthor($user)->setCreatedAt(new \DateTime())
        );
    }
}
