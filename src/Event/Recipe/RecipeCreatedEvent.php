<?php

namespace App\Event\Recipe;

use App\Entity\Recipe;

class RecipeCreatedEvent
{
    public function __construct(private readonly Recipe $recipe)
    {
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }
}
