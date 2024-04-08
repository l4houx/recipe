<?php

namespace App\Controller\Dashboard\Restaurant;

use App\Controller\BaseController;
use App\Entity\PaymentGateway;
use App\Entity\Traits\HasRoles;
use App\Form\RestaurantPayoutPaymentGatewayType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/restaurant/settings', name: 'dashboard_restaurant_settings_')]
#[IsGranted(HasRoles::RESTAURANT)]
class SettingsController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/scanner-app', name: 'scanner_app', methods: ['GET', 'POST'])]
    public function scannerApp(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('showRecipeDateStatsOnScannerApp', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => 'Show recipe date stats on the scanner app',
                'choices' => ['Yes' => 1, 'No' => 0],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => 'The recipe date stats (sales and attendance) will be visible on the scanner app',
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('allowTapToCheckInOnScannerApp', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => 'Allow tap to check in on the scanner app',
                'choices' => ['Yes' => 1, 'No' => 0],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => 'Besides the qr code scanning feature, the scanner account will be able to check in the attendees using a list and a button',
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $settings = $form->getData();
                $this->getUser()->getRestaurant()->setShowRecipeDateStatsOnScannerApp($settings['showRecipeDateStatsOnScannerApp']);
                $this->getUser()->getRestaurant()->setAllowTapToCheckInOnScannerApp($settings['allowTapToCheckInOnScannerApp']);

                $this->em->persist($this->getUser()->getRestaurant());
                $this->em->flush();
                $this->addFlash('success', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        } else {
            $form->get('showRecipeDateStatsOnScannerApp')->setData($this->getUser()->getRestaurant()->getShowRecipeDateStatsOnScannerApp());
            $form->get('allowTapToCheckInOnScannerApp')->setData($this->getUser()->getRestaurant()->getAllowTapToCheckInOnScannerApp());
        }

        return $this->render('dashboard/restaurant/settings/scanner-app.html.twig', compact('form'));
    }

    #[Route(path: '/payouts', name: 'payouts', methods: ['GET', 'POST'])]
    public function payout(): Response
    {
        return $this->render('dashboard/restaurant/settings/payout-methods.html.twig');
    }

    #[Route(path: '/payouts/new', name: 'payouts_new', methods: ['GET', 'POST'])]
    #[Route(path: '/payouts/{slug}/edit', name: 'payouts_edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    /*
    public function payoutNewEdit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $factoryName = $request->query->get('factoryName');
            if ('paypal_rest' != $factoryName && 'stripe_checkout' != $factoryName) {
                $this->addFlash('danger', $this->translator->trans('The payout method can not be found'));

                return $this->redirectToRoute('dashboard_restaurant_settings_payouts');
            }
            if (('paypal_rest' == $factoryName && 'no' == $this->settingService->getSettings('restaurant_payout_paypal_enabled')) || ('stripe_checkout' == $factoryName && 'no' == $this->settingService->getSettings('restaurant_payout_stripe_enabled'))) {
                $this->addFlash('danger', $this->translator->trans('This payout method is currently disabled'));

                return $this->redirectToRoute('dashboard_restaurant_settings_payouts');
            }
            $paymentgateway = new PaymentGateway();
            $form = $this->createForm(RestaurantPayoutPaymentGatewayType::class, $paymentgateway)->handleRequest($request);
        } else {
            /** @var PaymentGateway $paymentgateway /
            $paymentgateway = $this->settingService->getPaymentGateways(['restaurant' => $this->getUser()->getRestaurant()->getSlug(), 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$paymentgateway) {
                $this->addFlash('danger', $this->translator->trans('The payout method can not be found'));

                return $this->redirectToRoute('dashboard_restaurant_settings_payouts');
            }
            $form = $this->createForm(RestaurantPayoutPaymentGatewayType::class, $paymentgateway)->handleRequest($request);
        }

        if ($form->isSubmitted()) {
            /* @var PaymentGateway $paymentgateway /
            $paymentgateway->setGatewayName($paymentgateway->getFactoryName());
            if ($form->isValid()) {
                if (!$slug) {
                    $paymentgateway->setRestaurant($this->getUser()->getRestaurant());
                    $paymentgateway->setFactoryName($factoryName);
                    if ('paypal_rest' == $factoryName) {
                        $paymentgateway->setGatewayName('Paypal');
                        $paymentgateway->setName('Paypal');
                    } elseif ('stripe_checkout' == $factoryName) {
                        $paymentgateway->setGatewayName('Stripe');
                        $paymentgateway->setName('Stripe');
                    }
                }
                $paymentgateway->setUpdatedAt(new \DateTime());
                $this->em->persist($paymentgateway);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('The payout method has been successfully created'));
                } else {
                    $this->addFlash('success', $this->translator->trans('The payout method has been successfully updated'));
                }

                return $this->redirectToRoute('dashboard_restaurant_settings_payouts');
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/restaurant/settings/payout-add-edit.html.twig', compact('form', 'paymentgateway'));
    }
    */

    #[Route(path: '/payouts/{slug}/unset', name: 'payouts_unset', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function payoutUnset(?string $slug = null): RedirectResponse
    {
        /** @var PaymentGateway $paymentgateway */
        $paymentgateway = $this->settingService->getPaymentGateways(['restaurant' => $this->getUser()->getRestaurant()->getSlug(), 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$paymentgateway) {
            $this->addFlash('danger', $this->translator->trans('The payout method can not be found'));

            return $this->redirectToRoute('dashboard_restaurant_settings_payouts');
        }

        $paymentgateway->setIsOnline(false);

        $this->em->persist($paymentgateway);
        $this->em->flush();

        $this->addFlash('notice', $this->translator->trans('The payout method is unset'));

        return $this->redirectToRoute('dashboard_restaurant_settings_payouts');
    }
}
