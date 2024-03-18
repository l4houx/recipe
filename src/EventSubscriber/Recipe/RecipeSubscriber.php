<?php

namespace App\EventSubscriber\Recipe;

use App\Event\Account\AccountSuspendedEvent;
use App\Repository\RecipeRepository;
use App\Service\RecipeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RecipeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly EntityManagerInterface $em,
        private readonly RecipeService $recipeService
    ) {
    }

    public function cleanAccountContent(AccountSuspendedEvent $event): void
    {
        $this->recipeRepository->deleteForUser($event->getUser());
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AccountSuspendedEvent::class => 'cleanAccountContent',
        ];
    }
}
