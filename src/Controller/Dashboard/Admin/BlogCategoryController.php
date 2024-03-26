<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostFormType;
use App\Entity\PostCategory;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Security\Voter\PostVoter;
use App\Form\PostCategoryFormType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostCategoryRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

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
    //#[IsGranted(PostVoter::LIST)]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $rows = $this->postCategoryRepository->findForPagination($page);

        return $this->render('dashboard/admin/blog/category/index.html.twig', compact('rows'));
    }

    #[Route(path: '/categories/add', name: 'add', methods: ['GET', 'POST'])]
    #[Route(path: '/categories/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function addedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $postcategory = new PostCategory();
        } else {
            /** @var PostCategory $category */
            $postcategory = $this->settingService->getBlogPostCategories(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
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

        return $this->render('dashboard/admin/blog/category/add-edit.html.twig', compact('form', 'postcategory'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    //#[IsGranted(PostVoter::CREATE)]
    public function new(Request $request, #[CurrentUser] User $user): Response
    {
        $postcategory = new PostCategory();

        $form = $this->createForm(PostCategoryFormType::class, $postcategory)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($postcategory);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Content was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_blog_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/blog/category/new.html.twig', compact('postcategory', 'form'));
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function show(PostCategory $postcategory): Response
    {
        //$this->denyAccessUnlessGranted(PostVoter::SHOW, $postcategory, $this->translator->trans("Content can only be shown to their authors."));
    
        return $this->render('dashboard/admin/blog/category/show.html.twig', compact('postcategory'));
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    //#[IsGranted(PostVoter::MANAGE, subject: 'post', message: 'Content can only be edited by their authors.')]
    public function edit(Request $request, PostCategory $postcategory): Response
    {
        //$this->denyAccessUnlessGranted(PostVoter::MANAGE, $postcategory, $this->translator->trans("Content can only be edited by their authors."));

        $form = $this->createForm(PostCategoryFormType::class, $postcategory)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

            return $this->redirectToRoute('dashboard_admin_blog_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/blog/category/edit.html.twig', compact('postcategory', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    //#[IsGranted(PostVoter::MANAGE, subject: 'post')]
    public function delete(Request $request, PostCategory $postcategory): Response
    {
        if ($this->isCsrfTokenValid('post_category_deletion_'.$postcategory->getId(), $request->request->get('_token'))) {
            $this->em->remove($postcategory);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_blog_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
