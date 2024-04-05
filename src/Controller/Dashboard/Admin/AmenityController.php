<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Amenity;
use App\Entity\Traits\HasRoles;
use App\Form\AmenityFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/admin/manage-amenities', name: 'dashboard_admin_amenity_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class AmenityController extends AdminBaseController
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

        $rows = $paginator->paginate($this->settingService->getAmenities(['keyword' => $keyword, 'isOnline' => 'all', 'sort' => 'a.createdAt', 'order' => 'DESC']), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/amenity/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $amenity = new Amenity();
        } else {
            /** @var Amenity $amenity */
            $amenity = $this->settingService->getAmenities(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$amenity) {
                $this->addFlash('danger', $this->translator->trans('The amenity can not be found'));

                return $this->settingService->redirectToReferer('amenity');
            }
        }

        $form = $this->createForm(AmenityFormType::class, $amenity)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($amenity);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_amenity_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/amenity/new-edit.html.twig', compact('form', 'amenity'));
    }

    #[Route(path: '/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var Amenity $amenity */
        $amenity = $this->settingService->getAmenities(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$amenity) {
            $this->addFlash('danger', $this->translator->trans('The amenity can not be found'));

            return $this->settingService->redirectToReferer('amenity');
        }

        if (count($amenity->getVenues()) > 0) {
            $this->addFlash('danger', $this->translator->trans('The amenity can not be deleted because it is linked with one or more venues'));

            return $this->settingService->redirectToReferer('amenity');
        }

        if (null !== $amenity->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $amenity->setIsOnline(true);

        $this->em->persist($amenity);
        $this->em->flush();
        $this->em->remove($amenity);
        $this->em->flush();

        return $this->settingService->redirectToReferer('amenity');
    }

    #[Route(path: '/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Amenity $amenity */
        $amenity = $this->settingService->getAmenities(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$amenity) {
            $this->addFlash('danger', $this->translator->trans('The amenity can not be found'));

            return $this->settingService->redirectToReferer('amenity');
        }

        $amenity->setDeletedAt(null);

        $this->em->persist($amenity);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->settingService->redirectToReferer('amenity');
    }

    #[Route(path: '/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var Amenity $amenity */
        $amenity = $this->settingService->getAmenities(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$amenity) {
            $this->addFlash('danger', $this->translator->trans('The amenity can not be found'));

            return $this->settingService->redirectToReferer('amenity');
        }

        if (true === $amenity->getIsOnline()) {
            $amenity->setIsOnline(false);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $amenity->setIsOnline(true);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($amenity);
        $this->em->flush();

        return $this->settingService->redirectToReferer('amenity');
    }
}
