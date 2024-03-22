<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\User;
use App\Entity\Recipe;
use App\Form\RecipeFormType;
use App\Entity\Traits\HasRoles;
use App\Security\Voter\RecipeVoter;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Infrastructural\Messenger\Message\RecipePDFMessage;

#[Route('/%website_dashboard_path%/main-panel/manage-recipes', name: 'dashboard_admin_recipe_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class RecipeController extends AdminBaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RecipeRepository $recipeRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    #[IsGranted(RecipeVoter::LIST)]
    public function index(Request $request, Security $security): Response
    {
        $page = $request->query->getInt('page', 1);
        $userId = $this->getUser()->getId();
        $canListAll = $security->isGranted(RecipeVoter::LIST_ALL);
        $rows = $this->recipeRepository->findForPagination($page, $canListAll ? null : $userId);

        return $this->render('dashboard/admin/recipe/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted(RecipeVoter::CREATE)]
    public function new(Request $request, #[CurrentUser] User $user): Response
    {
        $recipe = new Recipe();
        $recipe->setAuthor($user);

        $form = $this->createForm(RecipeFormType::class, $recipe)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($recipe);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Content was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/recipe/new.html.twig', compact('recipe', 'form'));
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function show(Recipe $recipe): Response
    {
        $this->denyAccessUnlessGranted(RecipeVoter::SHOW, $recipe, $this->translator->trans("Content can only be shown to their authors."));
    
        return $this->render('dashboard/admin/recipe/show.html.twig', compact('recipe'));
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function edit(Request $request, Recipe $recipe, MessageBusInterface $messageBusInterface): Response
    {
        $this->denyAccessUnlessGranted(RecipeVoter::MANAGE, $this->translator->trans("Content can only be edited by their authors."));

        $form = $this->createForm(RecipeFormType::class, $recipe)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $messageBusInterface->dispatch(new RecipePDFMessage($recipe->getId()));

            $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

            return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/recipe/edit.html.twig', compact('recipe', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    #[IsGranted(RecipeVoter::MANAGE, subject: 'recipe')]
    public function delete(Request $request, Recipe $recipe): Response
    {
        if ($this->isCsrfTokenValid('recipe_deletion_'.$recipe->getId(), $request->request->get('_token'))) {
            $this->em->remove($recipe);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
