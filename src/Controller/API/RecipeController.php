<?php

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\Recipe;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecipeController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/get-recipes', name: 'get_recipes', methods: ['GET'])]
    public function getRecipes(Request $request, Packages $packages): Response
    {
        $q = '' == $request->query->get('q') ? 'all' : $request->query->get('q');
        $restaurant = '' == $request->query->get('restaurant') ? 'all' : $request->query->get('restaurant');
        $isOnline = '' == $request->query->get('isOnline') ? 'all' : $request->query->get('isOnline');
        $elapsed = '' == $request->query->get('elapsed') ? false : $request->query->get('elapsed');
        $limit = '' == $request->query->get('limit') ? 10 : $request->query->get('limit');

        if ('all' == $q) {
            $limit = 3;
        }

        $restaurantEnabled = true;
        if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
            $restaurantEnabled = 'all';
        }

        /** @var Recipe $recipes */
        $recipes = $this->settingService->getRecipes(['restaurant' => $restaurant, 'keyword' => $q, 'isOnline' => $isOnline, 'elapsed' => $elapsed, 'limit' => $limit, 'restaurantEnabled' => $restaurantEnabled])->getQuery()->getResult();

        $results = [];

        /** @var Recipe $recipe */
        foreach ($recipes as $recipe) {
            if ($elapsed = !'all' && !$recipe->hasAnRecipeDateOnSale()) {
                continue;
            }

            $venue = $this->translator->trans('Online');

            if ($recipe->getFirstOnSaleRecipeDate() && $recipe->getFirstOnSaleRecipeDate()->getVenue()) {
                $venue = $recipe->getFirstOnSaleRecipeDate()->getVenue()->getName().': '.$recipe->getFirstOnSaleRecipeDate()->getVenue()->stringifyAddress();
            }

            $date = $this->translator->trans('No recipes on sale');

            if ($recipe->getFirstOnSaleRecipeDate() && $recipe->getFirstOnSaleRecipeDate()->getStartdate()) {
                $date = date($this->getParameter('date_format_simple'), $recipe->getFirstOnSaleRecipeDate()->getStartdate()->getTimestamp());
            }

            $result = [
                'id' => $recipe->getSlug(),
                'text' => $recipe->getTitle(),
                'image' => $packages->getUrl($recipe->getImagePath()),
                'link' => $this->generateUrl('recipe', ['slug' => $recipe->getSlug(), '_locale' => $request->getLocale()]),
                'date' => $date,
                'venue' => $venue];
            array_push($results, $result);
        }

        return $this->json($results);
    }

    #[Route(path: '/get-recipe/{slug}', name: 'get_recipe', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function getRecipe(Request $request, ?string $slug = null): Response
    {
        $isOnline = '' == $request->query->get('isOnline') ? true : $request->query->get('isOnline');
        $elapsed = '' == $request->query->get('elapsed') ? false : $request->query->get('elapsed');

        $restaurantEnabled = true;

        if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
            $restaurantEnabled = 'all';
        }

        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug, 'isOnline' => $isOnline, 'elapsed' => $elapsed, 'restaurantEnabled' => $restaurantEnabled])->getQuery()->getOneOrNullResult();

        return $this->json(['slug' => $recipe->getSlug(), 'text' => $recipe->getTitle()]);
    }
}
