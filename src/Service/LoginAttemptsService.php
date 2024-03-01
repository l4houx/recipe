<?php

namespace App\Service;

use App\Entity\LoginAttempts;
use App\Entity\User;
use App\Repository\LoginAttemptsRepository;
use Doctrine\ORM\EntityManagerInterface;

class LoginAttemptsService
{
    final public const ATTEMPTS = 3;

    public function __construct(
        private readonly LoginAttemptsRepository $loginAttemptsRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function addAttempt(User $user): void
    {
        $attempts = (new LoginAttempts())->setUser($user);
        $this->em->persist($attempts);
        $this->em->flush();
    }

    public function limitReachedFor(User $user): bool
    {
        return $this->loginAttemptsRepository->countRecentFor($user, 30) >= self::ATTEMPTS;
    }
}
