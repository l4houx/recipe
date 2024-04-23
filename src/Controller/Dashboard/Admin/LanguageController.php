<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Setting\Language;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Form\LanguageFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/admin/manage-languages', name: 'dashboard_admin_language_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class LanguageController extends AdminBaseController
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

        $rows = $paginator->paginate($this->settingService->getLanguages(['keyword' => $keyword, 'isOnline' => 'all', 'sort' => 'l.createdAt', 'order' => 'DESC']), $request->query->getInt('page', 1), HasLimit::LANG_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/language/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $language = new Language();
        } else {
            /** @var Language $language */
            $language = $this->settingService->getHelpCenterArticles(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$language) {
                $this->addFlash('danger', $this->translator->trans('The language can not be found'));

                return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(LanguageFormType::class, $language)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($language);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/language/new-edit.html.twig', compact('form', 'language'));
    }

    #[Route(path: '/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var Language $language */
        $language = $this->settingService->getLanguages(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$language) {
            $this->addFlash('error', $this->translator->trans('The language can not be found'));

            return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        if (count($language->getRecipes()) > 0) {
            $this->addFlash('error', $this->translator->trans('The language can not be deleted because it is linked with one or more recipes'));

            return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $language->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $language->setIsOnline(true);

        $this->em->persist($language);
        $this->em->flush();
        $this->em->remove($language);
        $this->em->flush();
    }

    #[Route(path: '/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Language $language */
        $language = $this->settingService->getLanguages(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$language) {
            $this->addFlash('danger', $this->translator->trans('The language can not be found'));

            return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }
        $language->setDeletedAt(null);

        $this->em->persist($language);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var Language $language */
        $language = $this->settingService->getLanguages(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$language) {
            $this->addFlash('danger', $this->translator->trans('The language can not be found'));

            return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
        }

        if (false === $language->getIsOnline()) {
            $language->setIsOnline(true);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $language->setIsOnline(false);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($language);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_language_index', [], Response::HTTP_SEE_OTHER);
    }
}
