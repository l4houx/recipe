<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Audience;
use App\Entity\Traits\HasRoles;
use App\Form\AudienceFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/admin/manage-audiences', name: 'dashboard_admin_audience_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class AudienceController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');

        $rows = $paginator->paginate($this->settingService->getAudiences(['keyword' => $keyword, 'isOnline' => 'all', 'sort' => 'a.createdAt', 'order' => 'DESC']), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/audience/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $audience = new Audience();
        } else {
            /** @var Audience $audience */
            $audience = $this->settingService->getAudiences(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$audience) {
                $this->addFlash('danger', $this->translator->trans('The audience can not be found'));

                return $this->settingService->redirectToReferer('audience');
            }
        }

        $form = $this->createForm(AudienceFormType::class, $audience)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($audience);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_audience_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/audience/new-edit.html.twig', compact('form', 'audience'));
    }

    #[Route(path: '/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var Audience $audience */
        $audience = $this->settingService->getAudiences(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$audience) {
            $this->addFlash('danger', $this->translator->trans('The audience can not be found'));

            return $this->settingService->redirectToReferer('audience');
        }

        if (count($audience->getRecipes()) > 0) {
            $this->addFlash('danger', $this->translator->trans('The audience can not be deleted because it is linked with one or more recipes'));

            return $this->settingService->redirectToReferer('audience');
        }

        if (null !== $audience->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $audience->setIsOnline(true);

        $this->em->persist($audience);
        $this->em->flush();
        $this->em->remove($audience);
        $this->em->flush();

        return $this->settingService->redirectToReferer('audience');
    }

    #[Route(path: '/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Audience $audience */
        $audience = $this->settingService->getAudiences(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$audience) {
            $this->addFlash('danger', $this->translator->trans('The audience can not be found'));

            return $this->settingService->redirectToReferer('audience');
        }

        $audience->setDeletedAt(null);

        $this->em->persist($audience);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->settingService->redirectToReferer('audience');
    }

    #[Route(path: '/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var Audience $audience */
        $audience = $this->settingService->getAudiences(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$audience) {
            $this->addFlash('danger', $this->translator->trans('The audience can not be found'));

            return $this->settingService->redirectToReferer('audience');
        }

        if (true === $audience->getIsOnline()) {
            $audience->setIsOnline(false);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $audience->setIsOnline(true);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($audience);
        $this->em->flush();

        return $this->settingService->redirectToReferer('audience');
    }
}
