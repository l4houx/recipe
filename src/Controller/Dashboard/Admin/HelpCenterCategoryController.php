<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Entity\HelpCenterCategory;
use App\Form\HelpCenterCategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HelpCenterCategoryRepository;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/%website_dashboard_path%/main-panel/manage-help-center', name: 'dashboard_admin_help_center_category_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class HelpCenterCategoryController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/categories', name: 'index', methods: ['GET'])]
    /*
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = '' === $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $isOnline = '' === $request->query->get('isOnline') ? 'all' : $request->query->get('isOnline');

        $categories = $paginator->paginate($this->settingService->getHelpCenterCategories(['keyword' => $keyword, 'isOnline' => $isOnline, 'order' => 'c.createdAt', 'sort' => 'DESC']), $request->query->getInt('page', 1), HasLimit::HELPCENTERCATEGORY_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/helpCenter/category/index.html.twig', compact('categories'));
    }
    */

    public function index(Request $request, PaginatorInterface $paginator, HelpCenterCategoryRepository $helpCenterCategoryRepository): Response
    {
        $query = $helpCenterCategoryRepository->findBy(['isOnline' => true], ['createdAt' => 'DESC']);
        $page = $request->query->getInt('page', 1);

        $rows = $paginator->paginate($query, $page, HasLimit::HELPCENTERCATEGORY_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/helpCenter/category/index.html.twig', compact('rows'));
    }

    #[Route(path: '/categories/add', name: 'add', methods: ['GET', 'POST'])]
    #[Route(path: '/categories/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function addedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $category = new HelpCenterCategory();
        } else {
            /** @var HelpCenterCategory $category */
            $category = $this->settingService->getHelpCenterCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$category) {
                $this->addFlash('danger', $this->translator->trans('The article can not be found'));

                return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(HelpCenterCategoryType::class, $category)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($category);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
            }
            $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
        }

        return $this->render('dashboard/admin/helpCenter/category/add-edit.html.twig', compact('form', 'category'));
    }

    #[Route(path: '/categories/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/categories/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var HelpCenterCategory $category */
        $category = $this->settingService->getHelpCenterCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$category) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if (\count($category->getArticles()) > 0) {
            $this->addFlash('danger', $this->translator->trans('The category can not be deleted because it is linked with one or more help center articles.'));

            return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $category->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $category->setisOnline(true);
        $this->em->persist($category);
        $this->em->flush();
        $this->em->remove($category);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/categories/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var HelpCenterCategory $category */
        $category = $this->settingService->getHelpCenterCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$category) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
        }

        $category->setDeletedAt(null);
        $this->em->persist($category);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/categories/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/categories/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var HelpCenterCategory $category */
        $category = $this->settingService->getHelpCenterCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$category) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if (true === $category->getisOnline()) {
            $category->setisOnline(false);
            $this->addFlash('success', $this->translator->trans('Content is visible'));
        } else {
            $category->setisOnline(true);
            $this->addFlash('danger', $this->translator->trans('Content is hidden'));
        }

        $this->em->persist($category);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
