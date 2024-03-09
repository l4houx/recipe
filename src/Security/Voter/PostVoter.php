<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PostVoter extends Voter
{
    final public const LIST = 'LIST';
    final public const LIST_ALL = 'LIST_ALL';
    final public const CREATE = 'CREATE';
    final public const SHOW = 'SHOW';
    final public const MANAGE = 'MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            in_array($attribute, [self::CREATE, self::LIST, self::LIST_ALL]) ||
            (
                in_array($attribute, [self::SHOW, self::MANAGE])
                && $subject instanceof Post
            );
    }

    /**
     * @param Post|null $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::MANAGE => $this->canManage($user, $subject),
            self::LIST, self::CREATE, self::SHOW => true,
            default => false,
        };
    }

    private function canManage(User $user, Post $post): bool
    {
        return $user->isVerified() && $user == $post->getAuthor();
    }
}
