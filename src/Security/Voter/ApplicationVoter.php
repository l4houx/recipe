<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ApplicationVoter extends Voter
{
    public function __construct(private readonly string $appEnvironment)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return !in_array($attribute, ['IS_IMPERSONATOR']);
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ('prod' === $this->appEnvironment) {
            return 'superadmin' === $user->getUsername() && 1 === $user->getId();
        }

        return 'superadmin' === $user->getUsername();
    }
}
