<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Category;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/admin/manage-recipes', name: 'dashboard_admin_recipe_category_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class CategoryController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/categories', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // $page = $request->query->getInt('page', 1);
        // $rows = $this->categoryRepository->findForPagination($page);

        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $isFeatured = '' == $request->query->get('isFeatured') ? 'all' : $request->query->get('isFeatured');

        $rows = $paginator->paginate($this->settingService->getCategories(['keyword' => $keyword, 'isOnline' => 'all', 'isFeatured' => $isFeatured, 'sort' => 'c.createdAt', 'order' => 'DESC']), $request->query->getInt('page', 1), HasLimit::CATEGORY_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/recipes/category/index.html.twig', compact('rows'));
    }

    #[Route(path: '/categories/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/categories/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $category = new Category();
        } else {
            /** @var Category $category */
            $category = $this->settingService->getCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$category) {
                $this->addFlash('danger', $this->translator->trans('The category can not be found'));

                return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(CategoryFormType::class, $category)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($category);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/recipes/category/new-edit.html.twig', compact('form', 'category'));
    }

    #[Route(path: '/categories/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/categories/{slug}/delete', name: 'delete', methods: ['POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(Request $request, string $slug): Response
    {
        /** @var Category $category */
        $category = $this->settingService->getCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$category) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if (\count($category->getRecipes()) > 0) {
            $this->addFlash('danger', $this->translator->trans('The category can not be deleted because it is linked with one or more recipes'));

            return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $category->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        if ($this->isCsrfTokenValid('category_deletion_'.$category->getId(), $request->request->get('_token'))) {
            $category->setIsOnline(true);
            $category->setIsFeatured(false);

            $this->em->persist($category);
            $this->em->flush();
            $this->em->remove($category);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/categories/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Category $category */
        $category = $this->settingService->getCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$category) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
        }
        $category->setDeletedAt(null);

        $this->em->persist($category);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/categories/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/categories/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var Category $category */
        $category = $this->settingService->getCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$category) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if (false === $category->getIsOnline()) {
            $category->setIsOnline(true);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $category->setIsFeatured(false);
            $category->setIsOnline(true);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/categories/{slug}/featured', name: 'featured', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/categories/{slug}/notfeatured', name: 'notfeatured', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function featured(string $slug): Response
    {
        /** @var Category $category */
        $category = $this->settingService->getCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$category) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if (true === $category->getIsFeatured()) {
            $category->setIsFeatured(false);
            $category->setFeaturedorder(null);
            $this->addFlash('danger', $this->translator->trans('The category is not featured anymore and is removed from the homepage categories'));
        } else {
            $category->setIsFeatured(true);
            $this->addFlash('success', $this->translator->trans('The category is featured and is shown in the homepage categories'));
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_recipe_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
