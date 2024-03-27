<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Page;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\PageFormType;
use App\Repository\PageRepository;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-pages', name: 'dashboard_admin_page_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class PagesController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly PageRepository $pageRepository,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, #[CurrentUser] User $user, PaginatorInterface $paginator): Response
    {
        $query = $this->pageRepository->findAll();
        $page = $request->query->getInt('page', 1);

        $rows = $paginator->paginate(
            $query,
            $page,
            Page::PAGE_LIMIT
        );

        // $rows = $paginator->paginate($this->settingService->getPages([]), $request->query->getInt('page', 1), Page::PAGE_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/pages/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, #[CurrentUser] User $user, ?string $slug = null): Response
    {
        if (!$slug) {
            $page = new Page();
        } else {
            /** @var Page $page */
            $page = $this->settingService->getPages(['slug' => $slug])->getQuery()->getOneOrNullResult();
            /** @var Page $page */
            if (!$page) {
                $this->addFlash('danger', $this->translator->trans('The page can not be found'));

                return $this->redirectToRoute('dashboard_admin_page_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(PageFormType::class, $page)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($page);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_page_index', [], Response::HTTP_SEE_OTHER);
            }
            $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
        }

        return $this->render('dashboard/admin/pages/new-edit.html.twig', compact('page', 'form'));
    }

    #[Route(path: '/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    /*
    public function delete(Request $request, Page $page): Response
    {
        /** @var string|null $token /
        $token = $request->request->get('token');

        if (!$this->isCsrfTokenValid('delete', $token)) {
            return $this->redirectToRoute('dashboard_admin_page_index', [], Response::HTTP_SEE_OTHER);
        }

        if (!$page) {
            $this->addFlash('danger', $this->translator->trans('The page can not be found'));

            return $this->redirectToRoute('dashboard_admin_page_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->em->remove($page);
        $this->em->flush();

        $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));

        return $this->redirectToRoute('dashboard_admin_page_index', [], Response::HTTP_SEE_OTHER);
    }
    */

    public function delete(Request $request, Page $page): Response
    {
        if (!$page) {
            $this->addFlash('danger', $this->translator->trans('The page can not be found'));

            return $this->redirectToRoute('dashboard_admin_page_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('page_deletion_'.$page->getSlug(), $request->request->get('_token'))) {
            $this->em->remove($page);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_page_index', [], Response::HTTP_SEE_OTHER);
    }
}
