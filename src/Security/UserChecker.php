<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountSuspendedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Blocks user authentication.
 */
class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return; // @codeCoverageIgnore
        }

        if ($user->isSuspended()) {
            throw new AccountSuspendedException($user, $this->translator->trans('Votre compte a été suspendu.'));
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
