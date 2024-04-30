<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use App\Entity\PostType;
use App\Form\PostTypeFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/admin/manage-posts-types', name: 'dashboard_admin_post_type_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class PostTypeController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/types', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');

        $rows = $paginator->paginate($this->settingService->getPostsTypes(['keyword' => $keyword, 'isOnline' => 'all', 'sort' => 'p.createdAt', 'order' => 'DESC']), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/post/types/index.html.twig', compact('rows'));
    }

    #[Route(path: '/types/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/types/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $type = new PostType();
        } else {
            /** @var PostType $type */
            $type = $this->settingService->getPostsTypes(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$type) {
                $this->addFlash('danger', $this->translator->trans('The post type can not be found'));

                return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(PostTypeFormType::class, $type)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($type);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/post/types/new-edit.html.twig', compact('form', 'type'));
    }

    #[Route(path: '/types/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/types/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var PostType $type */
        $type = $this->settingService->getPostsTypes(['hidden' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$type) {
            $this->addFlash('danger', $this->translator->trans('The post type can not be found'));

            return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
        }

        if (count($type->getPosts()) > 0) {
            $this->addFlash('danger', $this->translator->trans('The post type can not be deleted because it is linked with one or more posts'));

            return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $type->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $type->setIsOnline(true);

        $this->em->persist($type);
        $this->em->flush();
        $this->em->remove($type);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/types/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var PostType $type */
        $type = $this->settingService->getPostsTypes(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$type) {
            $this->addFlash('danger', $this->translator->trans('The post type can not be found'));

            return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
        }

        $type->setDeletedAt(null);

        $this->em->persist($type);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/types/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/types/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var PostType $type */
        $type = $this->settingService->getPostsTypes(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$type) {
            $this->addFlash('danger', $this->translator->trans('The post type can not be found'));

            return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
        }

        if (true === $type->getIsOnline()) {
            $type->setIsOnline(false);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $type->setIsOnline(true);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($type);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_post_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
