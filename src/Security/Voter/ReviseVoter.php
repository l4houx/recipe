<?php

namespace App\Security\Voter;

use App\Entity\Content;
use App\Entity\Revise;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReviseVoter extends Voter
{
    final public const ADD = 'ADD';
    final public const DELETE = 'DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::ADD,
            self::DELETE,
        ]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if (
            self::ADD === $attribute
            && $subject instanceof Content
            && $subject->isOnline()
        ) {
            return true;
        }

        if (
            self::DELETE === $attribute
            && $subject instanceof Revise
            && $subject->getAuthor()->getId() === $user->getId()
        ) {
            return true;
        }

        return false;
    }
}
