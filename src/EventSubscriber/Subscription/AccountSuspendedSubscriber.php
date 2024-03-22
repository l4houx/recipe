<?php

declare(strict_types=1);

namespace App\EventSubscriber\Subscription;

use App\Event\Account\AccountSuspendedEvent;
use App\Security\Exception\PremiumNotBanException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountSuspendedSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AccountSuspendedEvent::class => 'onAccountSuspended',
        ];
    }

    public function onAccountSuspended(AccountSuspendedEvent $event): void
    {
        if ($event->getUser()->isPremium()) {
            throw new PremiumNotBanException();
        }
    }
}
