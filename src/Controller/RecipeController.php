<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Repository\RecipeRepository;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;

class RecipeController extends BaseController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/recipes', name: 'recipes', methods: ['GET'])]
    public function recipes(Request $request, PaginatorInterface $paginator): Response
    {
        $category = "all";
        $categorySlug = $request->query->get('category') == "" ? "all" : $request->query->get('category');
        $keyword = $request->query->get('keyword') == "" ? "all" : $request->query->get('keyword');
        $localonly = $request->query->get('localonly') == "" ? "all" : $request->query->get('localonly');
        $country = $request->query->get('country') == "" ? "all" : $request->query->get('country');
        $location = $request->query->get('location') == "" ? "all" : $request->query->get('location');
        $startdate = $request->query->get('startdate') == "" ? "all" : $request->query->get('startdate');
        $freeonly = $request->query->get('freeonly') == "" ? "all" : $request->query->get('freeonly');
        $pricemin = $request->query->get('pricemin') == "" || $request->query->get('pricemin') == "0" ? "all" : $request->query->get('pricemin');
        $pricemax = $request->query->get('pricemax') == "" || $request->query->get('pricemax') == "10000" ? "all" : $request->query->get('pricemax');
        $audience = $request->query->get('audience') == "" ? "all" : $request->query->get('audience');
        $restaurant = $request->query->get('restaurant') == "" ? "all" : $request->query->get('restaurant');
        $onlineonly = $request->query->get('onlineonly') == "" ? "all" : $request->query->get('onlineonly');

        if ('all' != $categorySlug) {
            $category = $this->settingService->getCategories(['slug' => $categorySlug])->getQuery()->getOneOrNullresult();
            if (!$category) {
                $this->addFlash('danger', $this->translator->trans('The category can not be found'));

                return $this->redirectToRoute('recipes', [], Response::HTTP_SEE_OTHER);
            }
        }

        $rows = $paginator->paginate($this->settingService->getRecipes(['category' => $categorySlug, 'keyword' => $keyword, 'localonly' => $localonly, 'country' => $country, 'location' => $location, 'startdate' => $startdate, 'pricemin' => $pricemin, 'pricemax' => $pricemax, 'audience' => $audience, 'restaurant' => $restaurant, 'freeonly' => $freeonly, 'onlineonly' => $onlineonly])->getQuery(), $request->query->getInt('page', 1), $this->settingService->getSettings('recipes_per_page'), ['wrap-queries' => true]);

        /*$query = $this->recipeRepository->findBy(['isOnline' => true], ['createdAt' => 'DESC']);
        $page = $request->query->getInt('page', 1);

        $rows = $paginator->paginate(
            $query,
            $page,
            $this->settingService->getSettings('recipes_per_page'),
            ['wrap-queries' => true]
        );*/

        return $this->render('recipe/recipes.html.twig', compact('rows', 'category'));
    }

    #[Route(path: '/recipe/{slug}', name: 'recipe', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function recipe(Request $request, string $slug, EntityManagerInterface $em): Response
    {
        if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
            /** @var Recipe $recipe */
            $recipe = $this->settingService->getRecipes(['slug' => $slug, 'elapsed' => 'all', 'isOnline' => 'all'])->getQuery()->getOneOrNullResult();
        } elseif ($this->isGranted(HasRoles::RESTAURANT)) {
            /** @var Recipe $recipe */
            $recipe = $this->settingService->getRecipes(['slug' => $slug, 'elapsed' => 'all', 'isOnline' => 'all'])->getQuery()->getOneOrNullResult();

            if ($recipe) {
                if ($recipe->getRestaurant() != $this->getUser()->getRestaurant() && false == $recipe->getIsOnline()) {
                    $recipe = null;
                }
            }
        } else {
            /** @var Recipe $recipe */
            $recipe = $this->settingService->getRecipes(['slug' => $slug, 'elapsed' => 'all'])->getQuery()->getOneOrNullResult();
        }

        if (!$recipe) {
            $this->addFlash('danger', $this->translator->trans('The recipe not be found'));

            return $this->redirectToRoute('recipes', [], Response::HTTP_SEE_OTHER);
        }

        $recipe->viewed();
        $em->persist($recipe);
        $em->flush();

        return $this->render('recipe/recipe.html.twig', compact('recipe'));
    }

    #[Route(path: '/categories', name: 'categories', methods: ['GET'])]
    public function categories(): Response
    {
        return $this->render('recipe/categories.html.twig');
    }
}
