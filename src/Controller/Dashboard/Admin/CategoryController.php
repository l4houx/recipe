<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Entity\Traits\HasRoles;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/%website_dashboard_path%/main-panel/manage-categories', name: 'dashboard_admin_category_')]
#[IsGranted(HasRoles::ADMIN)]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $categories = $this->categoryRepository->findForPagination($page);

        return $this->render('dashboard/admin/category/index.html.twig', compact('categories'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($category);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Content was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/category/new.html.twig', compact('category', 'form'));
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

            return $this->redirectToRoute('dashboard_admin_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/category/edit.html.twig', compact('category', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('category_deletion_'.$category->getId(), $request->request->get('_token'))) {
            $this->em->remove($category);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
