<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\Venue;
use App\Form\VenueFormType;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Controller\BaseController;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class VenueController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService,
        private readonly AuthorizationCheckerInterface $authChecker
    ) {
    }

    #[Route(path: '/admin/manage-venues', name: 'dashboard_admin_venue_index', methods: ['GET'])]
    #[Route(path: '/restaurant/my-venues', name: 'dashboard_restaurant_venue_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $directory = '' == $request->query->get('directory') ? 'all' : $request->query->get('directory');

        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()?->getSlug();
        }

        $rows = $paginator->paginate($this->settingService->getVenues(['restaurant' => $restaurant, 'keyword' => $keyword, 'directory' => $directory, 'isOnline' => 'all', 'restaurantEnabled' => 'all']), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/shared/venue/index.html.twig', compact('rows'));
    }

    #[Route(path: '/admin/manage-venues/new', name: 'dashboard_admin_venue_new', methods: ['GET', 'POST'])]
    #[Route(path: '/admin/manage-venues/{slug}/edit', name: 'dashboard_admin_venue_edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-venues/new', name: 'dashboard_restaurant_venue_new', methods: ['GET', 'POST'])]
    #[Route(path: '/restaurant/my-venues/{slug}/edit', name: 'dashboard_restaurant_venue_edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function addedit(Request $request, ?string $slug = null): Response
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()?->getSlug();
        }

        if (!$slug) {
            $venue = new Venue();
        } else {
            /** @var Venue $venue */
            $venue = $this->settingService->getVenues(['restaurant' => $restaurant, 'isOnline' => 'all', 'slug' => $slug, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();
            if (!$venue) {
                $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

                return $this->settingService->redirectToReferer('venue');
            }
        }

        $form = $this->createForm(VenueFormType::class, $venue)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
                    $venue->setRestaurant($this->getUser()->getRestaurant());
                }

                foreach ($venue->getImages() as $image) {
                    $image->setVenue($venue);
                }

                $this->em->persist($venue);
                $this->em->flush();

                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                    if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
                        return $this->redirectToRoute('dashboard_admin_venue_index', [], Response::HTTP_SEE_OTHER);
                    } else {
                        return $this->redirectToRoute('dashboard_restaurant_venue_index', [], Response::HTTP_SEE_OTHER);
                    }
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                    if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
                        return $this->redirectToRoute('dashboard_admin_venue_index', [], Response::HTTP_SEE_OTHER);
                    } else {
                        return $this->redirectToRoute('dashboard_restaurant_venue_index', [], Response::HTTP_SEE_OTHER);
                    }
                }
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/venue/new-edit.html.twig', compact('form', 'venue'));
    }

    #[Route(path: '/admin/manage-venues/{slug}/disable', name: 'dashboard_admin_venue_disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/admin/manage-venues/{slug}/delete', name: 'dashboard_admin_venue_delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-venues/{slug}/disable', name: 'dashboard_restaurant_venue_disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-venues/{slug}/delete', name: 'dashboard_restaurant_venue_delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['restaurant' => $restaurant, 'isOnline' => 'all', 'slug' => $slug, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$venue) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        if (count($venue->getRecipeDates()) > 0) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be deleted because it is linked with one or more recipes'));

            return $this->settingService->redirectToReferer('venue');
        }

        if (null !== $venue->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $venue->setIsOnline(true);
        $venue->setIsListedondirectory(false);

        $this->em->persist($venue);
        $this->em->flush();
        $this->em->remove($venue);
        $this->em->flush();

        return $this->settingService->redirectToReferer('venue');
    }

    #[Route(path: '/admin/manage-venues/{slug}/restore', name: 'dashboard_admin_venue_restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['restaurant' => 'all', 'isOnline' => 'all', 'slug' => $slug, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$venue) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        $venue->setDeletedAt(null);

        $this->em->persist($venue);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->settingService->redirectToReferer('venue');
    }

    #[Route(path: '/admin/manage-venues/{slug}/show', name: 'dashboard_admin_venue_show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/admin/manage-venues/{slug}/hide', name: 'dashboard_admin_venue_hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-venues/{slug}/show', name: 'dashboard_restaurant_venue_show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-venues/{slug}/hide', name: 'dashboard_restaurant_venue_hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['restaurant' => $restaurant, 'isOnline' => 'all', 'slug' => $slug, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$venue) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        if (true === $venue->getIsOnline()) {
            $venue->setIsOnline(false);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $venue->setIsOnline(true);
            $venue->setIsListedondirectory(false);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($venue);
        $this->em->flush();

        return $this->settingService->redirectToReferer('venue');
    }

    #[Route(path: '/admin/manage-venues/listondirectory', name: 'dashboard_admin_venue_listondirectory', methods: ['GET'])]
    #[Route(path: '/admin/manage-venues/{slug}/hidefromdirectory', name: 'dashboard_admin_venue_hidefromdirectory', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function publicvenuesdirectory(string $slug): Response
    {
        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['isOnline' => 'all', 'slug' => $slug, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$venue) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        if (true === $venue->getIsListedondirectory()) {
            $venue->setIsListedondirectory(false);
            $this->addFlash('info', $this->translator->trans('The venue is hidden from the public venues directory'));
        } else {
            $venue->setIsListedondirectory(true);
            $this->addFlash('success', $this->translator->trans('The venue is listed on the public venues directory'));
        }

        $this->em->persist($venue);
        $this->em->flush();

        return $this->settingService->redirectToReferer('venue');
    }
}
