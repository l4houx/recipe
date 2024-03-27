<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\PostCategory;
use App\Entity\Traits\HasRoles;
use App\Form\PostCategoryFormType;
use App\Repository\PostCategoryRepository;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/main-panel/manage-blogs', name: 'dashboard_admin_blog_category_')]
#[IsGranted(HasRoles::TEAM)]
class BlogCategoryController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService,
        private readonly PostCategoryRepository $postCategoryRepository
    ) {
    }

    #[Route(path: '/categories', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $rows = $this->postCategoryRepository->findForPagination($page);

        return $this->render('dashboard/admin/blog/category/index.html.twig', compact('rows'));
    }

    #[Route(path: '/categories/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/categories/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $postcategory = new PostCategory();
        } else {
            /* @var PostCategory $postcategory */
            //$postcategory = $this->settingService->getBlogPostCategories([/*'isOnline' => 'all',*/ 'slug' => $slug])->getQuery()->getOneOrNullResult();

            $postcategory = $this->postCategoryRepository->findOneBy(['slug' => $slug], []);
            if (!$postcategory) {
                $this->addFlash('danger', $this->translator->trans('The article can not be found'));

                return $this->redirectToRoute('dashboard_admin_help_center_category_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(PostCategoryFormType::class, $postcategory)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($postcategory);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_blog_category_index', [], Response::HTTP_SEE_OTHER);
            }
            $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
        }

        return $this->render('dashboard/admin/blog/category/new-edit.html.twig', compact('form', 'postcategory'));
    }

    //#[Route(path: '/categories/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    #[Route(path: '/categories/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/categories/{slug}/delete', name: 'delete', methods: ['POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(Request $request, PostCategory $postcategory): Response
    {
        if (!$postcategory) {
            $this->addFlash('danger', $this->translator->trans('The category can not be found'));

            return $this->redirectToRoute('dashboard_admin_blog_category_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('post_category_deletion_'.$postcategory->getSlug(), $request->request->get('_token'))) {
            $this->em->remove($postcategory);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_blog_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
