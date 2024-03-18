<?php

namespace App\Service;

use App\Entity\Recipe;
use App\Event\Recipe\PreRecipeCreatedEvent;
use App\Event\Recipe\RecipeCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class RecipeService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * Create a new recipe.
     */
    public function createRecipe(Recipe $recipe): void
    {
        $recipe->setCreatedAt(new \DateTimeImmutable());
        $recipe->setUpdatedAt(new \DateTimeImmutable());

        $this->dispatcher->dispatch(new PreRecipeCreatedEvent($recipe));

        $this->em->persist($recipe);
        $this->em->flush();

        $this->dispatcher->dispatch(new RecipeCreatedEvent($recipe));
    }

    /**
     * Update a recipe.
     */
    public function updateRecipe(Recipe $recipe): void
    {
        $recipe->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();
    }
}
