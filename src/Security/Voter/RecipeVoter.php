<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Recipe;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecipeVoter extends Voter
{
    final public const LIST = 'LIST';
    final public const LIST_ALL = 'LIST_ALL';
    final public const CREATE = 'CREATE';
    final public const SHOW = 'SHOW';
    final public const MANAGE = 'MANAGE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            in_array($attribute, [self::CREATE, self::LIST, self::LIST_ALL]) ||
            (
                in_array($attribute, [self::SHOW, self::MANAGE])
                && $subject instanceof Recipe
            );
    }

    /**
     * @param Recipe|null $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::LIST:
            case self::CREATE:
            case self::SHOW:
                return true;
                //return $user->isVerified();
                break;

            case self::MANAGE:
                return $subject->getAuthor()->getId() === $user->getId();
                //return $this->canManage();
                break;
        }

        return false;

        /*
        return match ($attribute) {
            self::MANAGE => $this->canManage($user, $subject),
            self::LIST, self::CREATE, self::SHOW => true,
            default => false,
        };
        */
    }

    private function canManage(User $user, Recipe $recipe): bool
    {
        return $recipe->getAuthor()->getId() === $user->getId();
        return $user->isVerified() && $user == $recipe->getAuthor();
    }
}
