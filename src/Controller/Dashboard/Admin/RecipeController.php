<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/%website_dashboard_path%/main-panel/manage-recipes', name: 'dashboard_admin_recipe_')]
class RecipeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RecipeRepository $recipeRepository,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('dashboard/admin/recipe/index.html.twig', [
            'recipes' => $this->recipeRepository->findWithDurationLowerThan(20),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($recipe);
            $this->em->flush();

            $this->addFlash('success', 'La recette a été créé avec succès.');

            return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/recipe/new.html.twig', compact('recipe', 'form'));
    }

    #[Route('/{slug}-{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+', 'slug' => '[a-z0-9-]+'])]
    public function show(Request $request, string $slug, int $id): Response
    {
        $recipe = $this->recipeRepository->find($id);

        if ($recipe->getSlug() !== $slug) {
            return $this->redirectToRoute('recipe_show', ['slug' => $recipe->getSlug(), 'id' => $recipe->getId()]);
        }

        return $this->render('dashboard/admin/recipe/show.html.twig', compact('recipe'));
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', 'La recette a été modifié avec succès.');

            return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/recipe/edit.html.twig', compact('recipe', 'form'));
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
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
