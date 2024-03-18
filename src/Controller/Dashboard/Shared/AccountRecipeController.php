<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\Recipe;
use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use App\Security\Voter\RecipeVoter;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/%website_dashboard_path%/account/my-recipes', name: 'dashboard_account_recipe_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountRecipeController extends Controller
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly RecipeRepository $recipeRepository
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    #[IsGranted(RecipeVoter::LIST)]
    public function index(Request $request, Security $security): Response
    {
        $page = $request->query->getInt('page', 1);
        $userId = $this->getUser()->getId();
        $canListAll = $security->isGranted(RecipeVoter::LIST_ALL);
        $recipes = $this->recipeRepository->findForPagination($page, $canListAll ? null : $userId);

        return $this->render('dashboard/shared/recipe/index.html.twig', compact('recipes'));
    }

    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'])]
    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[IsGranted(RecipeVoter::CREATE)]
    public function addedit(): Response
    {
        // Code

        return $this->render('dashboard/shared/recipe/add-edit.html.twig');
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted(RecipeVoter::CREATE)]
    public function new(): Response
    {
        return $this->render('dashboard/shared/recipe/new.html.twig');
    }

    #[Route(path: '/{slug}', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function show(Recipe $recipe): Response
    {
        $this->denyAccessUnlessGranted(RecipeVoter::SHOW, $recipe, $this->translator->trans("Recipes can only be shown to their authors."));

        return $this->render('dashboard/shared/recipe/show.html.twig', compact('recipe'));
    }

    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    //#[IsGranted(RecipeVoter::MANAGE, subject: 'recipe', message: 'Recipes can only be edited by their authors.')]
    public function edit(): Response
    {
        $this->denyAccessUnlessGranted(RecipeVoter::MANAGE, $this->translator->trans("Recipes can only be edited by their authors."));

        return $this->render('dashboard/shared/recipe/edit.html.twig');
    }

    #[Route(path: '/{slug}/delete', name: 'delete', methods: ['POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[IsGranted(RecipeVoter::MANAGE, subject: 'recipe')]
    public function delete(Request $request, Recipe $recipe): Response
    {
        if ($this->isCsrfTokenValid('recipe_deletion_'.$recipe->getSlug(), $request->request->get('_token'))) {
            $this->em->remove($recipe);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Recipe was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_account_recipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
