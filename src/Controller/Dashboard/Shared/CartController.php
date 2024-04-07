<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\CartElement;
use App\Entity\RecipeSubscription;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class CartController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/creator/cart', name: 'dashboard_creator_cart', methods: ['GET'])]
    public function cart(Request $request): Response
    {
        // Remove previous subscription reservations
        if (count($this->getUser()->getSubscriptionReservations())) {
            foreach ($this->getUser()->getSubscriptionReservations() as $subscriptionreservation) {
                $this->em->remove($subscriptionreservation);
            }
            $this->em->flush();
        }

        // Check recipe sale status
        foreach ($this->getUser()->getCartelements() as $cartelement) {
            if (!$cartelement->getRecipesubscription()->isOnSale()) {
                $this->em->remove($cartelement);
                $this->em->flush();
                $this->addFlash('info', $this->translator->trans('Your cart has been automatically updated because one or more recipes are no longer on sale'));

                return $this->redirectToRoute('dashboard_creator_cart', [], Response::HTTP_SEE_OTHER);
            }

            if (!$cartelement->getRecipesubscription()->isOnSale()) {
                $this->em->remove($cartelement);
                $this->em->flush();
                $this->addFlash('info', $this->translator->trans('Your cart has been automatically updated because one or more recipes are no longer on sale'));

                return $this->redirectToRoute('dashboard_creator_cart', [], Response::HTTP_SEE_OTHER);
            }

            if ($cartelement->getRecipesubscription()->getSubscriptionsLeftCount() > 0 && $cartelement->getQuantity() > $cartelement->getRecipesubscription()->getSubscriptionsLeftCount()) {
                $cartelement->setQuantity($cartelement->getRecipesubscription()->getSubscriptionsLeftCount());
                $this->em->persist($cartelement);
                $this->em->flush();
                $this->addFlash('info', $this->translator->trans('Your cart has been automatically updated because one or more recipe\'s quotas has changed'));

                return $this->redirectToRoute('dashboard_creator_cart', [], Response::HTTP_SEE_OTHER);
            }
        }

        if ('POST' == $request->getMethod()) {
            if (0 == count($request->request->all())) {
                $this->addFlash('info', $this->translator->trans('No subscriptions selected to add to cart'));
            } else {
                foreach ($request->request->all() as $subscriptionreference => $subscriptionquantity) {
                    $cartelement = $this->getUser()->getCartelementByRecipeSubscriptionReference($subscriptionreference);
                    if (!$cartelement) {
                        $this->addFlash('danger', $this->translator->trans('The recipe subscription can not be found'));

                        return $this->render('dashboard/creator/cart/cart.html.twig');
                    }
                    $cartelement->setQuantity($subscriptionquantity);
                    $this->em->persist($cartelement);
                }
                $this->em->flush();
                $this->addFlash('success', $this->translator->trans('Content was edited successfully.'));
            }
        }

        return $this->render('dashboard/creator/cart/cart.html.twig');
    }

    #[Route(path: '/creator/cart/add', name: 'dashboard_creator_cart_add', methods: ['GET', 'POST'])]
    #[Route(path: '/pointofsale/cart/add', name: 'dashboard_pointofsale_cart_add', methods: ['GET', 'POST'])]
    public function add(Request $request): Response
    {
        $reservedSeats = [];
        if ($request->query->get('seats')) {
            $reservedSeats = json_decode($request->query->get('seats'));
        }
        if (count($reservedSeats) > 0) {
            foreach ($reservedSeats as $seatIndex => $reservedSeat) {
                $reservedSeats[$seatIndex]->assigned = false;
            }
        }

        foreach ($request->request->all() as $subscriptionreference => $subscriptionquantity) {
            if ('' != $subscriptionquantity && intval($subscriptionquantity) > 0) {
                /** @var RecipeSubscription $recipesubscription */
                $recipesubscription = $this->em->getRepository("App\Entity\RecipeSubscription")->findOneByReference($subscriptionreference);

                if (!$recipesubscription) {
                    $this->addFlash('danger', $this->translator->trans('The recipe subscription can not be found'));

                    return $this->settingService->redirectToReferer();
                }
                if (!$recipesubscription->isOnSale()) {
                    $this->addFlash('danger', $recipesubscription->stringifyStatus());

                    return $this->settingService->redirectToReferer('cart');
                }

                $cartelement = new CartElement();
                $cartelement->setUser($this->getUser());
                $cartelement->setRecipesubscription($recipesubscription);
                $cartelement->setQuantity(intval($subscriptionquantity));

                if ($this->getUser()->hasRole(HasRoles::CREATOR) && !$cartelement->getRecipesubscription()->getIsFree()) {
                    $cartelement->setSubscriptionFee($this->settingService->getSettings('subscription_fee_online'));
                } elseif ($this->getUser()->hasRole(HasRoles::POINTOFSALE) && !$cartelement->getRecipesubscription()->getIsFree()) {
                    $cartelement->setSubscriptionFee($this->settingService->getSettings('subscription_fee_pos'));
                }

                $thisSubscriptionReservedSeats = [];
                if (count($reservedSeats) > 0) {
                    foreach ($reservedSeats as $seatIndex => $reservedSeat) {
                        if ($reservedSeat->relativeSubscriptionReference == $subscriptionreference && false == $reservedSeats[$seatIndex]->assigned) {
                            $reservedSeats[$seatIndex]->assigned = true;
                            $thisSubscriptionReservedSeats[] = $reservedSeat;
                        }
                    }
                }
                if (count($thisSubscriptionReservedSeats) > 0) {
                    $cartelement->setReservedSeats($thisSubscriptionReservedSeats);
                }

                $this->em->persist($cartelement);
            }
        }

        $this->em->flush();

        if ($this->isGranted(HasRoles::CREATOR)) {
            $this->addFlash('success', $this->translator->trans('The subscriptions has been successfully added to your cart'));

            return $this->redirectToRoute('dashboard_creator_cart', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->redirectToRoute('dashboard_pointofsale_checkout', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route(path: '/creator/cart/{id}/remove', name: 'dashboard_creator_cart_remove', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(int $id): Response
    {
        /** @var CartElement $cartelement */
        $cartelement = $this->em->getRepository("App\Entity\CartElement")->find($id);
        if ($cartelement->getUser() != $this->getUser()) {
            $this->addFlash('danger', $this->translator->trans('Access is denied. You may not have the appropriate permissions to access this resource.'));

            return $this->settingService->redirectToReferer('cart');
        }

        $this->em->remove($cartelement);
        $this->em->flush();

        $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

        return $this->settingService->redirectToReferer('cart');
    }

    #[Route(path: '/creator/cart/empty', name: 'dashboard_creator_cart_empty', methods: ['GET'])]
    public function emptyCart(): Response
    {
        $this->settingService->emptyCart($this->getUser());
        $this->addFlash('info', $this->translator->trans('Your cart has been emptied'));

        return $this->settingService->redirectToReferer('cart');
    }
}
