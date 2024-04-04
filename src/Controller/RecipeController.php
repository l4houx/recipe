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
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/recipes', name: 'recipes', methods: ['GET'])]
    public function recipes(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->recipeRepository->findBy(['isOnline' => true], ['createdAt' => 'DESC']);
        $page = $request->query->getInt('page', 1);

        $rows = $paginator->paginate(
            $query,
            $page,
            HasLimit::RECIPE_LIMIT
        );

        return $this->render('recipe/recipes.html.twig', compact('rows'));
    }

    /*
    #[Route(path: '/recipes/{slug}-{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT, 'slug' => Requirement::ASCII_SLUG])]
    public function show(Request $request, string $slug, int $id, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $recipe = $this->recipeRepository->find($id);

        if ($recipe->getSlug() !== $slug) {
            return $this->redirectToRoute('recipe_show', [
                'id' => $recipe->getId(),
                'slug' => $recipe->getSlug(),
            ], 301);
        }

        if (!$recipe) {
            $this->addFlash('secondary', $translator->trans('The recipe not be found'));
            return $this->redirectToRoute('recipe_index');
        }

        $recipe->viewed();
        $em->persist($recipe);
        $em->flush();

        return $this->render('recipe/show.html.twig', compact('recipe'));
    }
    */

    #[Route(path: '/recipe/{slug}', name: 'recipe', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function recipe(Request $request, string $slug, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        if ($this->isGranted(HasRoles::APPLICATION)) {
            /** @var Recipe $recipe */
            $recipe = $this->settingService->getRecipes(['slug' => $slug, 'elapsed' => 'all', 'isOnline' => 'all'])->getQuery()->getOneOrNullResult();
        } elseif ($this->isGranted(HasRoles::RESTAURANT)) {
            /** @var Recipe $recipe */
            $recipe = $this->settingService->getRecipes(['slug' => $slug, 'elapsed' => 'all', 'published' => 'all'])->getQuery()->getOneOrNullResult();

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
            $this->addFlash('danger', $translator->trans('The recipe not be found'));

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
