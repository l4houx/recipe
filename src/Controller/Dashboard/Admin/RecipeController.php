<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\User;
use App\Entity\Recipe;
use App\Form\RecipeFormType;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/%website_dashboard_path%/main-panel/manage-recipes', name: 'dashboard_admin_recipe_')]
#[IsGranted(HasRoles::TEAM)]
class RecipeController extends Controller
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RecipeRepository $recipeRepository
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    #[IsGranted(RecipeVoter::LIST)]
    public function index(Request $request, Security $security): Response
    {
        $page = $request->query->getInt('page', 1);
        $userId = $this->getUser()->getId();
        $canListAll = $security->isGranted(RecipeVoter::LIST_ALL);
        $recipes = $this->recipeRepository->findForPagination($page, $canListAll ? null : $userId);

        return $this->render('dashboard/admin/recipe/index.html.twig', compact('recipes'));
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted(RecipeVoter::CREATE)]
    public function new(Request $request, #[CurrentUser] User $user): Response
    {
        $recipe = new Recipe();
        $recipe->setAuthor($user);

        $form = $this->createForm(RecipeFormType::class, $recipe)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($recipe);
            $this->em->flush();

            $this->addFlash('success', 'La recette a été créé avec succès.');

            return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/recipe/new.html.twig', compact('recipe', 'form'));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function show(Recipe $recipe): Response
    {
        $this->denyAccessUnlessGranted(RecipeVoter::SHOW, $recipe, "Les recettes ne peuvent être montrées qu'à leurs auteurs.");
    
        return $this->render('dashboard/admin/recipe/show.html.twig', compact('recipe'));
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    #[IsGranted(RecipeVoter::MANAGE, subject: 'recipe', message: 'Les recettes ne peuvent être modifiées que par leurs auteurs.')]
    public function edit(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(RecipeFormType::class, $recipe)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', 'La recette a été modifié avec succès.');

            return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/recipe/edit.html.twig', compact('recipe', 'form'));
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    #[IsGranted(RecipeVoter::MANAGE, subject: 'recipe')]
    public function delete(Request $request, Recipe $recipe): Response
    {
        if ($this->isCsrfTokenValid('recipe_deletion_'.$recipe->getId(), $request->request->get('_token'))) {
            $this->em->remove($recipe);
            $this->em->flush();

            $this->addFlash('danger', 'La recette a été supprimé avec succès.');
        }

        return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
