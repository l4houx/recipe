<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\Venue;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Entity\VenueSeatingPlan;
use App\Controller\BaseController;
use App\Form\VenueSeatingPlanFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/%website_dashboard_path%/restaurant/my-venues', name: 'dashboard_restaurant_venue_seating_plans_')]
#[IsGranted(HasRoles::DEFAULT)]
class SeatingPlanController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/{venueSlug}/seating-plans', name: 'index', methods: ['GET'], requirements: ['venueSlug' => Requirement::ASCII_SLUG])]
    public function index(Request $request, string $venueSlug): Response
    {
        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['restaurant' => $this->getUser()->getRestaurant()->getSlug(), 'isOnline' => 'all', 'slug' => $venueSlug])->getQuery()->getOneOrNullResult();
        if (!$venue) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        $seatingPlans = $this->settingService->getVenuesSeatingPlans(['venue' => $venue->getSlug()])->getQuery()->getResult();

        return $this->render('dashboard/shared/venue/seatingPlans/index.html.twig', compact('venue', 'seatingPlans'));
    }

    #[Route(path: '/{venueSlug}/seating-plans/new', name: 'new', methods: ['GET', 'POST'], requirements: ['venueSlug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{venueSlug}/seating-plans/{seatingPlanSlug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['venueSlug' => Requirement::ASCII_SLUG, 'seatingPlanSlug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, string $venueSlug, ?string $seatingPlanSlug = null): Response
    {
        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['restaurant' => $this->getUser()->getRestaurant()->getSlug(), 'isOnline' => 'all', 'slug' => $venueSlug])->getQuery()->getOneOrNullResult();
        if (!$venue) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        if (!$seatingPlanSlug) {
            $seatingPlan = new VenueSeatingPlan();
            $seatingPlan->setVenue($venue);
        } else {
            /** @var VenueSeatingPlan $seatingPlan */
            $seatingPlan = $this->settingService->getVenuesSeatingPlans(['slug' => $seatingPlanSlug])->getQuery()->getOneOrNullResult();

            if (!$seatingPlan) {
                $this->addFlash('danger', $this->translator->trans('The seating plan can not be found'));

                return $this->settingService->redirectToReferer('venue');
            }

            if (count($seatingPlan->getRecipeDates()) > 0) {
                $this->addFlash('danger', $this->translator->trans('The seating plan can not be edited after it is assigned to one or more recipe dates'));

                return $this->settingService->redirectToReferer('venue');
            }
        }

        $form = $this->createForm(VenueSeatingPlanFormType::class, $seatingPlan)->handleRequest($request);

        if ($form->isSubmitted()) {
            foreach ($venue->getSeatingPlans() as $existentVenueSeatingPlan) {
                if ($existentVenueSeatingPlan != $seatingPlan && $seatingPlan->getName() == $existentVenueSeatingPlan->getName()) {
                    $form->get('translations')->addError(new \Symfony\Component\Form\FormError($this->translator->trans('The seating plan name has to be unique per venue')));
                }
            }

            if ($form->isValid()) {
                $seatingPlan->setDesign(json_decode($request->request->get('venue_seating_plan')['design']));
                $seatingPlan->setUpdatedAt(new \DateTime());

                $this->em->persist($seatingPlan);
                $this->em->flush();

                if (!$seatingPlanSlug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_restaurant_venue_seating_plans_index', ['venueSlug' => $venueSlug]);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/venue/seatingPlans/new-edit.html.twig', compact('seatingPlan', 'venue', 'form'));
    }

    #[Route(path: '/{venueSlug}/seating-plans/{seatingPlanSlug}/duplicate', name: 'duplicate', methods: ['GET'], requirements: ['venueSlug' => Requirement::ASCII_SLUG, 'seatingPlanSlug' => Requirement::ASCII_SLUG])]
    public function duplicate(Request $request, string $venueSlug, ?string $seatingPlanSlug = null): Response
    {
        /** @var Venue $venue */
        $venue = $this->settingService->getVenues(['restaurant' => $this->getUser()->getRestaurant()->getSlug(), 'isonline' => 'all', 'slug' => $venueSlug])->getQuery()->getOneOrNullResult();

        if (!$venue) {
            $this->addFlash('danger', $this->translator->trans('The venue can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        /** @var VenueSeatingPlan $seatingPlan */
        $seatingPlan = $this->settingService->getVenuesSeatingPlans(['slug' => $seatingPlanSlug])->getQuery()->getOneOrNullResult();
        if (!$seatingPlan) {
            $this->addFlash('danger', $this->translator->trans('The seating plan can not be found'));

            return $this->settingService->redirectToReferer('venue');
        }

        $seatingPlanDuplicated = new VenueSeatingPlan();
        $seatingPlanDuplicated->translate($seatingPlan->getDefaultLocale())->setName($seatingPlan->getName().' - '.$this->translator->trans('Duplicated'));
        $seatingPlanDuplicated->setDesign($seatingPlan->getDesign());
        $seatingPlanDuplicated->setVenue($seatingPlan->getVenue());
        $seatingPlanDuplicated->setUpdatedAt(new \DateTime());
        $this->em->persist($seatingPlanDuplicated);
        $seatingPlanDuplicated->mergeNewTranslations();
        $this->em->flush();
        $this->addFlash('success', $this->translator->trans('The seating plan has been successfully duplicated'));

        return $this->redirectToRoute('dashboard_restaurant_venue_seating_plans_index', ['venueSlug' => $venueSlug]);
    }
}
