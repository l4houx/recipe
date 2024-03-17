<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Event\Account\AccountDeletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class AccountDeletedService
{
    final public const DAYS = 7;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly SecurityService $securityService
    ) {
    }

    public function accountDeleted(User $user, Request $request): void
    {
        $this->securityService->logout($request);
        $this->dispatcher->dispatch(new AccountDeletedEvent($user));
        $user->setDeletedAt(new \DateTimeImmutable('+ '.(string) self::DAYS.' days'));
        $this->em->flush();
    }
}
