<?php

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\RecipeSubscription;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class RecipeSubscriptionController extends BaseController
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/recipe/{recipeSlug}/recipe-date/{recipeDateReference}/get-subscriptions', name: 'get_recipesubscriptions_by_recipedate', methods: ['GET'], requirements: ['recipeSlug' => Requirement::ASCII_SLUG])]
    public function getRecipeSubscriptions(Request $request, string $recipeSlug, string $recipeDateReference): Response
    {
        if ($this->isGranted(HasRoles::CREATOR) || !$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $restaurant = 'all';

        if ($this->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        $limit = '' == $request->query->get('limit') ? 10 : $request->query->get('limit');

        /** @var RecipeSubscription $recipeSubscriptions */
        $recipeSubscriptions = $this->settingService->getRecipeSubscriptions(['restaurant' => $restaurant, 'recipe' => $recipeSlug, 'recipedate' => $recipeDateReference, 'limit' => $limit])->getQuery()->getResult();
        $results = [];

        /** @var RecipeSubscription $recipeSubscription */
        foreach ($recipeSubscriptions as $recipeSubscription) {
            $result = [
                'id' => $recipeSubscription->getReference(),
                'text' => $recipeSubscription->getName()];
            array_push($results, $result);
        }

        return $this->json($results);
    }

    #[Route(path: '/get-recipe-subscription/{reference}', name: 'get_recipesubscription', methods: ['GET'])]
    public function getRecipeSubscription(?string $reference = null): Response
    {
        if ($this->isGranted(HasRoles::CREATOR) || !$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedHttpException();
        }

        $restaurant = 'all';

        if ($this->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        $recipeSubscription = $this->settingService->getRecipeSubscriptions(['restaurant' => $restaurant, 'reference' => $reference])->getQuery()->getOneOrNullResult();

        if (!$recipeSubscription) {
            return $this->json([]);
        }

        return $this->json(['id' => $recipeSubscription->getReference(), 'text' => $recipeSubscription->getName()]);
    }
}
