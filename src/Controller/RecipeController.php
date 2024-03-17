<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Traits\HasLimit;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(name: 'recipe_')]
class RecipeController extends AbstractController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository
    ) {
    }

    #[Route(path: '/recipes', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->recipeRepository->findBy(['isOnline' => true], ['createdAt' => 'DESC']);
        $page = $request->query->getInt('page', 1);

        $pagination = $paginator->paginate(
            $query,
            $page,
            HasLimit::RECIPE_LIMIT
        );

        return $this->render('recipe/index.html.twig', compact('pagination'));
    }

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
}
