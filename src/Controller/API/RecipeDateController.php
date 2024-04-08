<?php

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\Recipe;
use App\Entity\RecipeDate;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class RecipeDateController extends BaseController
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/recipe/{slug}/get-recipe-dates', name: 'get_recipedates_by_recipe', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function getRecipeDates(Request $request, string $slug): Response
    {
        if ($this->isGranted(HasRoles::CREATOR) || !$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $restaurant = 'all';

        if ($this->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        $limit = '' == $request->query->get('limit') ? 10 : $request->query->get('limit');

        /** @var RecipeDate $recipeDates */
        $recipeDates = $this->settingService->getRecipeDates(['restaurant' => $restaurant, 'recipe' => $slug])->getQuery()->getResult();

        $results = [];

        /** @var RecipeDate $recipeDate */
        foreach ($recipeDates as $recipeDate) {
            $result = [
                'id' => $recipeDate->getReference(),
                'text' => $recipeDate->getRecipe()->getTitle().' ('.date($this->getParameter('date_format_simple'), $recipeDate->getStartdate()->getTimestamp()).')'];
            array_push($results, $result);
        }

        return $this->json($results);
    }

    #[Route(path: '/get-recipe-date/{reference}', name: 'get_recipedate', methods: ['GET'])]
    public function getRecipeDate(?string $reference = null): Response
    {
        if ($this->isGranted(HasRoles::CREATOR) || !$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $restaurant = 'all';

        if ($this->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['restaurant' => $restaurant, 'reference' => $reference])->getQuery()->getOneOrNullResult();

        if (!$recipeDate) {
            return $this->json([]);
        }

        return $this->json(['id' => $recipeDate->getReference(), 'text' => $recipeDate->getRecipe()->getTitle().' ('.date($this->getParameter('date_format_simple'), $recipeDate->getStartdate()->getTimestamp()).')']);
    }

    #[Route(path: '/dashboard/pointofsale/get-recipe-dates-onsale', name: 'dashboard_pointofsale_get_recipedates', methods: ['GET'])]
    public function getPosRecipeDates(): Response
    {
        if (!$this->isGranted(HasRoles::POINTOFSALE)) {
            throw new AccessDeniedHttpException();
        }

        /** @var Recipe $recipes */
        $recipes = $this->settingService->getRecipes(['onsalebypos' => $this->getUser()->getPointofsale()])->getQuery()->getResult();

        $results = [];

        /** @var Recipe $recipe */
        foreach ($recipes as $recipe) {
            if ($recipe->hasAnRecipeDateOnSale()) {
                foreach ($recipe->getRecipedates() as $recipeDate) {
                    if ($recipeDate->isOnSaleByPos($this->getUser()->getPointofsale())) {
                        $result = [
                            'id' => $recipeDate->getReference(),
                            'text' => $recipeDate->getRecipe()->getTitle().' ('.date($this->getParameter('date_format_simple'), $recipeDate->getStartdate()->getTimestamp()).')'];
                        array_push($results, $result);
                    }
                }
            }
        }

        return $this->json($results);
    }
}
