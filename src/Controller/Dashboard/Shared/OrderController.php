<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Recipe;
use App\Entity\SubscriptionReservation;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Payum\Core\Request\GetHumanStatus;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OrderController extends BaseController
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
        private readonly Environment $templating,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/%website_dashboard_path%/creator/checkout', name: 'dashboard_creator_checkout', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/pointofsale/checkout', name: 'dashboard_pointofsale_checkout', methods: ['GET'])]
    public function checkout(Request $request, RouterInterface $router): Response
    {
        if ($this->isGranted(HasRoles::CREATOR)) {
            $paymentGateways = $this->settingService->getPaymentGateways([])->getQuery()->getResult();
            $form = $this->createForm(CheckoutFormType::class, null, ['validation_groups' => 'creator'])->handleRequest($request);
        } else {
            $form = $this->createForm(CheckoutFormType::class, null, ['validation_groups' => 'pos'])->handleRequest($request);
        }

        /** @var Order $order */
        $order = null;

        if ($form->isSubmitted()) {
            /** @var Order $order */
            $order = $this->settingService->getOrders(['status' => 0, 'reference' => $form->getData()['orderReference']])->getQuery()->getOneOrNullResult();

            if ($form->isValid()) {
                // Recheck order
                if (!$order) {
                    $this->addFlash('danger', $this->translator->trans('The order can not be found'));

                    return $this->redirectToRoute('dashboard_main');
                }

                // Recheck order elements
                if (!count($order->getOrderelements())) {
                    $this->addFlash('danger', $this->translator->trans('You order is empty'));

                    return $this->redirectToRoute('dashboard_main');
                }

                foreach ($order->getOrderelements() as $orderelement) {
                    // Check recipe sale status
                    if (!$orderelement->getRecipeSubscription()->isOnSale()) {
                        $this->settingService->handleCanceledPayment($order->getReference(), $this->translator->trans('Your order has been automatically canceled because one or more recipes are no longer on sale'));
                        $this->addFlash('info', $this->translator->trans('Your order has been automatically canceled because one or more recipes are no longer on sale'));

                        return $this->redirectToRoute('dashboard_main');
                    }
                    // Check recipe quotas
                    if ($orderelement->getRecipesubscription()->getSubscriptionsLeftCount(true, $this->getUser()) > 0 && $orderelement->getQuantity() > $orderelement->getRecipesubscription()->getSubscriptionsLeftCount(true, $this->getUser())) {
                        $this->settingService->handleCanceledPayment($order->getReference(), $this->translator->trans('Your order has been automatically canceled because one or more recipe\'s quotas has changed'));
                        $this->addFlash('info', $this->translator->trans('Your order has been automatically canceled because one or more recipe\'s quotas has changed'));

                        return $this->redirectToRoute('dashboard_main');
                    }

                    // Check subscription reservations
                    foreach ($orderelement->getSubscriptionReservations() as $subscriptionReservation) {
                        if ($subscriptionReservation->isExpired()) {
                            $this->settingService->handleCanceledPayment($order->getReference(), $this->translator->trans('Your order has been automatically canceled because your subscription reservations has been released'));
                            $this->addFlash('info', $this->translator->trans('Your order has been automatically canceled because your subscription reservations has been released'));

                            return $this->redirectToRoute('dashboard_main');
                        }
                    }

                    // Check seats
                    if ($orderelement->getRecipeSubscription()->getRecipeDate()->getHasSeatingPlan()) {
                        foreach ($orderelement->getReservedSeats() as $reservedSeat) {
                            if ($orderelement->getRecipeSubscription()->isSeatAlreadyReservedByIds($reservedSeat['sectionId'], $reservedSeat['rowId'], $reservedSeat['seatNumber'])) {
                                $this->settingService->handleCanceledPayment($order->getReference(), $this->translator->trans('Your order has been automatically canceled because your subscription reservations has been released'));
                                $this->addFlash('info', $this->translator->trans('Your order has been automatically canceled because one or more seats were already reserved'));

                                return $this->redirectToRoute('dashboard_main');
                            }
                        }
                    }
                }

                $storage = $request->get('payum')->getStorage('App\Entity\Payment');

                $orderTotalAmount = $order->getOrderElementsPriceSum(true);

                if (0 == $orderTotalAmount) {
                    $paymentGateway = $this->em->getRepository("App\Entity\PaymentGateway")->findOneBySlug('free');
                    $gatewayFactoryName = 'offline';
                } elseif ($this->isGranted(HasRoles::CREATOR)) {
                    if (0 == count($paymentGateways)) {
                        $this->addFlash('danger', $this->translator->trans('No payment gateways are currently enabled'));

                        return $this->redirectToRoute('dashboard_attendee_cart');
                    }
                    $gatewayFactoryName = $request->request->get('payment_gateway');
                    $paymentGateway = $this->settingService->getPaymentGateways(['gatewayFactoryName' => $gatewayFactoryName])->getQuery()->getOneOrNullResult();
                } else {
                    $paymentGateway = $this->em->getRepository("App\Entity\PaymentGateway")->findOneBySlug('point-of-sale');
                    $gatewayFactoryName = 'offline';
                }

                if (!$paymentGateway) {
                    $this->addFlash('danger', $this->translator->trans('The payment gateway can not be found'));

                    return $this->redirectToRoute('dashboard_main');
                }

                // Sets the choosen payment gateway
                $order->setPaymentGateway($paymentGateway);
                $this->em->persist($order);

                if ($order->getPayment()) {
                    $payment = $order->getPayment();
                } else {
                    $payment = $storage->create();
                }
                // Sets the amount to be paid
                $orderamount = intval(bcmul($orderTotalAmount, 100));

                $payment->setOrder($order);
                $payment->setNumber($this->settingService->generateReference(20));
                $payment->setCurrencyCode($this->settingService->getSettings('currency_ccy'));
                $payment->setTotalAmount($orderamount); // 1.23 USD = 123
                $payment->setDescription($this->translator->trans('Payment of subscriptions purchased on %website_name%', ['%website_name%' => $this->settingService->getSettings('website_name')]));
                $payment->setClientId($this->getUser()->getId());

                if (null != $form->getData()['firstname'] && '' != $form->getData()['firstname']) {
                    $payment->setFirstname($form->getData()['firstname']);
                }

                if (null != $form->getData()['lastname'] && '' != $form->getData()['lastname']) {
                    $payment->setLastname($form->getData()['lastname']);
                }

                if ($this->isGranted(HasRoles::CREATOR)) {
                    $payment->setClientEmail($form->getData()['email']);
                    $payment->setCountry($form->getData()['country']);
                    $payment->setState($form->getData()['state']);
                    $payment->setCity($form->getData()['city']);
                    $payment->setPostalcode($form->getData()['postalcode']);
                    $payment->setStreet($form->getData()['street']);
                    $payment->setStreet2($form->getData()['street2']);
                }

                $storage->update($payment);
                $order->setPayment($payment);
                $this->em->flush();

                if ($this->isGranted(HasRoles::CREATOR)) {
                    if ('offline' == $request->request->get('payment_gateway')) {
                        $this->addFlash('success', $this->translator->trans('Your order has been successfully placed, please proceed to the payment as explained in the instructions'));

                        return $this->redirectToRoute('dashboard_creator_order_details', ['reference' => $payment->getOrder()->getReference()]);
                    } else {
                        if ('flutterwave' == $request->request->get('payment_gateway')) {
                            return $this->redirectToRoute('dashboard_attendee_checkout_flutterwave_redirect_to_payment_url', ['orderReference' => $payment->getOrder()->getReference()]);
                        } elseif ('mercadopago' == $request->request->get('payment_gateway')) {
                            return $this->redirectToRoute('dashboard_attendee_checkout_mercadopago_create_preference', ['orderReference' => $payment->getOrder()->getReference()]);
                        } else {
                            $captureToken = $request->get('payum')->getTokenFactory()->createCaptureToken(
                                $gatewayFactoryName, $payment, 'dashboard_creator_checkout_done'
                            );
                        }
                    }
                } else {
                    $captureToken = $request->get('payum')->getTokenFactory()->createCaptureToken(
                        $gatewayFactoryName, $payment, 'dashboard_pointofsale_checkout_done'
                    );
                }

                return $this->redirect($captureToken->getTargetUrl());
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));

                if ($this->isGranted(HasRoles::CREATOR)) {
                    return $this->render('dashboard/creator/order/checkout.html.twig', compact('form', 'order', 'paymentGateways'));
                } else {
                    return $this->render('dashboard/pointOfSale/order/checkout.html.twig', compact('form', 'order'));
                }
            }
        } else {
            if (!$request->query->get('orderReference')) {
                // Check referer
                $referer = $request->headers->get('referer');
                if (!\is_string($referer) || !$referer) {
                    $this->addFlash('info', $this->translator->trans('You must review your cart before proceeding to checkout'));

                    return $this->redirectToRoute('dashboard_main');
                }

                if ($this->isGranted(HasRoles::CREATOR)) {
                    if ('dashboard_attendee_cart' != $router->match(Request::create($referer)->getPathInfo())['_route']) {
                        $this->addFlash('info', $this->translator->trans('You must review your cart before proceeding to checkout'));

                        return $this->redirectToRoute('dashboard_main');
                    }
                }

                // Recheck cart status
                if (!count($this->getUser()->getCartelements())) {
                    $this->addFlash('danger', $this->translator->trans('Your cart is empty'));

                    return $this->redirectToRoute('dashboard_main');
                }

                // Check recipe sale status
                foreach ($this->getUser()->getCartelements() as $cartelement) {
                    if (!$cartelement->getRecipeSubscription()->isOnSale()) {
                        $this->em->remove($cartelement);
                        $this->em->flush();
                        $this->addFlash('info', $this->translator->trans('Your cart has been automatically updated because one or more recipes are no longer on sale'));

                        return $this->redirectToRoute('dashboard_main');
                    }

                    if ($cartelement->getRecipeSubscription()->getSubscriptionsLeftCount() > 0 && $cartelement->getQuantity() > $cartelement->getRecipeSubscription()->getSubscriptionsLeftCount()) {
                        $cartelement->setQuantity($cartelement->getRecipeSubscription()->getSubscriptionsLeftCount());
                        $this->em->persist($cartelement);
                        $this->em->flush();
                        $this->addFlash('info', $this->translator->trans('Your cart has been automatically updated because one or more recipe\'s quotas has changed'));

                        return $this->redirectToRoute('dashboard_main');
                    }
                }

                // Remove previous subscription reservations
                if (count($this->getUser()->getSubscriptionReservations())) {
                    foreach ($this->getUser()->getSubscriptionReservations() as $subscriptionreservation) {
                        $this->em->remove($subscriptionreservation);
                    }
                    $this->em->flush();
                }

                $order = $this->settingService->transformCartIntoOrder($this->getUser());
                if (!$order) {
                    $this->addFlash('danger', $this->translator->trans('The order can not be found'));

                    return $this->redirectToRoute('dashboard_main');
                }

                $this->em->persist($order);
                $this->em->flush();
                $this->settingService->emptyCart($this->getUser());

                // Create new subscription reservations according to current cart
                foreach ($order->getOrderElements() as $orderElement) {
                    $subscriptionreservation = new SubscriptionReservation();
                    $subscriptionreservation->setRecipeSubscription($orderElement->getRecipeSubscription());
                    $subscriptionreservation->setUser($this->getUser());
                    $subscriptionreservation->setOrderElement($orderElement);
                    $subscriptionreservation->setQuantity($orderElement->getQuantity());
                    $expiresAt = new \DateTime();
                    $subscriptionreservation->setExpiresAt($expiresAt->add(new \DateInterval('PT'.$this->settingService->getSettings('checkout_timeleft').'S')));
                    $orderElement->addSubscriptionReservation($subscriptionreservation);
                    $this->em->persist($subscriptionreservation);
                    $this->em->flush();
                }
            } else {
                $order = $this->settingService->getOrders(['status' => 0, 'reference' => $request->query->get('orderReference')])->getQuery()->getOneOrNullResult();
                if (!$order) {
                    $this->addFlash('danger', $this->translator->trans('The order can not be found'));

                    return $this->redirectToRoute('dashboard_main');
                }
            }
        }

        if ($this->isGranted(HasRoles::CREATOR)) {
            return $this->render('dashboard/creator/order/checkout.html.twig', compact('form', 'paymentGateways', 'order'));
        } else {
            return $this->render('dashboard/pointOfSale/order/checkout.html.twig', compact('form', 'order'));
        }
    }

    #[Route(path: '/%website_dashboard_path%/creator/checkout/done', name: 'dashboard_creator_checkout_done', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/pointofsale/checkout/done', name: 'dashboard_pointofsale_checkout_done', methods: ['GET'])]
    public function done(Request $request): Response
    {
        // Remove subscription reservations
        if (count($this->getUser()->getSubscriptionReservations())) {
            foreach ($this->getUser()->getSubscriptionReservations() as $subscriptionreservation) {
                $this->em->remove($subscriptionreservation);
            }
            $this->em->flush();
        }

        try {
            $token = $request->get('payum')->getHttpRequestVerifier()->verify($request);
            $gateway = $request->get('payum')->getGateway($token->getGatewayName());
        } catch (\Exception $e) {
            $this->addFlash('danger', $this->translator->trans('An danger has occured while processing your request'));

            return $this->redirectToRoute('dashboard_main');
        }

        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();
        $request->get('payum')->getHttpRequestVerifier()->invalidate($token);

        if ($status->isCaptured() || $status->isAuthorized() || $status->isPending()) {
            $this->settingService->handleSuccessfulPayment($payment->getOrder()->getReference());

            if ($payment->getOrder()->getOrderElementsPriceSum() > 0) {
                $this->addFlash('success', $this->translator->trans('Your payment has been successfully processed'));
            } else {
                $this->addFlash('success', $this->translator->trans('Your registration has been successfully processed'));
            }

            if ($this->isGranted(HasRoles::CREATOR)) {
                return $this->redirectToRoute('dashboard_creator_order_details', ['reference' => $payment->getOrder()->getReference()]);
            } else {
                return $this->redirectToRoute('dashboard_pointofsale_order_details', ['reference' => $payment->getOrder()->getReference()]);
            }
        } elseif ($status->isFailed()) {
            $this->settingService->handleFailedPayment($payment->getOrder()->getReference());
            $this->addFlash('danger', $this->translator->trans('Your payment could not be processed at this time'));

            if ($this->isGranted(HasRoles::CREATOR)) {
                return $this->redirectToRoute('dashboard_creator_checkout_failure', ['number' => $payment->getNumber()]);
            } else {
                return $this->redirectToRoute('dashboard_pointofsale_index');
            }
        } elseif ($status->isCanceled()) {
            $this->settingService->handleCanceledPayment($payment->getOrder()->getReference());
            $this->addFlash('danger', $this->translator->trans('Your payment operation was canceled'));

            if ($this->isGranted(HasRoles::CREATOR)) {
                return $this->redirectToRoute('dashboard_creator_orders');
            } else {
                return $this->redirectToRoute('dashboard_pointofsale_index');
            }
        } else {
            return $this->redirectToRoute('dashboard_main');
        }

        if ($this->isGranted(HasRoles::CREATOR)) {
            return $this->render('dashboard/creator/order/failure.html.twig', [
                'status' => $status->getValue(),
                'paymentdetails' => $payment->getDetails(),
            ]);
        } else {
            return $this->redirectToRoute('dashboard_main');
        }
    }

    #[Route(path: '/%website_dashboard_path%/creator/checkout/failure/{number}', name: 'dashboard_creator_checkout_failure', methods: ['GET'])]
    public function failure(Request $request, $number): Response
    {
        $referer = $request->headers->get('referer');
        if (!\is_string($referer) || !$referer || 'dashboard_creator_checkout_done' != $referer) {
            return $this->redirectToRoute('dashboard_creator_orders');
        }

        $payment = $this->settingService->getPayments(['number' => $number])->getQuery()->getOneOrNullResult();
        if (!$payment) {
            $this->addFlash('danger', $this->translator->trans('The payment can not be found'));

            return $this->redirectToRoute('dashboard_creator_orders');
        }

        return $this->render('dashboard/creator/order/failure.html.twig', [
            'paymentdetails' => $payment->getDetails(),
        ]);
    }

    #[Route(path: '/%website_dashboard_path%/creator/my-subscriptions', name: 'dashboard_creator_orders', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/pointofsale/my-orders', name: 'dashboard_pointofsale_orders', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/admin/manage-orders', name: 'dashboard_admin_orders', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/restaurant/manage-orders', name: 'dashboard_restaurant_orders', methods: ['GET'])]
    public function orders(Request $request, PaginatorInterface $paginator, AuthorizationCheckerInterface $authChecker): Response
    {
        $reference = '' == $request->query->get('reference') ? 'all' : $request->query->get('reference');
        $recipe = '' == $request->query->get('recipe') ? 'all' : $request->query->get('recipe');
        $recipedate = '' == $request->query->get('recipedate') ? 'all' : $request->query->get('recipedate');
        $recipesubscription = '' == $request->query->get('recipesubscription') ? 'all' : $request->query->get('recipesubscription');
        $user = '' == $request->query->get('user') ? 'all' : $request->query->get('user');
        $restaurant = '' == $request->query->get('restaurant') ? 'all' : $request->query->get('restaurant');
        $datefrom = '' == $request->query->get('datefrom') ? 'all' : $request->query->get('datefrom');
        $dateto = '' == $request->query->get('dateto') ? 'all' : $request->query->get('dateto');
        $status = '' == $request->query->get('status') ? 'all' : $request->query->get('status');
        $paymentgateway = '' == $request->query->get('paymentgateway') ? 'all' : $request->query->get('paymentgateway');

        $authChecker->isGranted(HasRoles::CREATOR) ? $status = 'all' : $upcomingsubscriptions = 'all';

        $ordersQuery = $this->settingService->getOrders(['reference' => $reference, 'recipe' => $recipe, 'recipedate' => $recipedate, 'recipesubscription' => $recipesubscription, 'user' => $user, 'restaurant' => $restaurant, 'datefrom' => $datefrom, 'dateto' => $dateto, 'status' => $status, 'paymentgateway' => $paymentgateway])->getQuery();

        // Export current orders query results into Excel / Csv
        if (($authChecker->isGranted(HasRoles::ADMINAPPLICATION) || $authChecker->isGranted(HasRoles::RESTAURANT) || $authChecker->isGranted(HasRoles::POINTOFSALE)) && ('1' == $request->query->get('excel') || '1' == $request->query->get('csv') || '1' == $request->query->get('pdf'))) {
            $orders = $ordersQuery->getResult();
            if (!count($orders)) {
                $this->addFlash('danger', $this->translator->trans('No orders found to be included in the report'));

                return $this->settingService->redirectToReferer('orders');
            }

            if ('1' == $request->query->get('excel') || '1' == $request->query->get('csv')) {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle($this->settingService->getSettings('website_name').' '.$this->translator->trans('orders summary'));
                $fileName = $this->settingService->getSettings('website_name').' '.$this->translator->trans('orders summary').'.xlsx';
                $temp_file = tempnam(sys_get_temp_dir(), $fileName);

                $sheet->setCellValue('A3', $this->translator->trans('Order reference'));
                $sheet->setCellValue('B3', $this->translator->trans('Order status'));
                $sheet->setCellValue('C3', $this->translator->trans('Order Date'));
                $sheet->setCellValue('D3', $this->translator->trans('Restaurant / Recipe / Date / Subscription'));
                $sheet->setCellValue('E3', $this->translator->trans('First Name'));
                $sheet->setCellValue('F3', $this->translator->trans('Last Name'));
                $sheet->setCellValue('G3', $this->translator->trans('Email'));
                $sheet->setCellValue('H3', $this->translator->trans('Quantity'));
                $sheet->setCellValue('I3', $this->translator->trans('Amount').'('.$this->settingService->getSettings('currency_ccy').')');
                $sheet->setCellValue('J3', $this->translator->trans('Payment'));
                $sheet->setCellValue('K3', $this->translator->trans('Street'));
                $sheet->setCellValue('L3', $this->translator->trans('Street 2'));
                $sheet->setCellValue('M3', $this->translator->trans('City'));
                $sheet->setCellValue('N3', $this->translator->trans('State'));
                $sheet->setCellValue('O3', $this->translator->trans('Zip / Postal code'));
                $sheet->setCellValue('P3', $this->translator->trans('Country'));
                $sheet->setCellValue('Q3', $this->translator->trans('Attendee status'));

                $i = 5;
                $totalSales = 0;
                $totalAttendees = 0;

                /** @var Order $order */
                foreach ($orders as $order) {
                    foreach ($order->getOrderElements() as $orderElement) {
                        if ($authChecker->isGranted(HasRoles::ADMINAPPLICATION) || ($authChecker->isGranted(HasRoles::RESTAURANT) && $this->getUser()->getRestaurant() == $orderElement->getRecipeSubscription()->getRecipeDate()->getRecipe()->getRestaurant()) || $this->isGranted(HasRoles::POINTOFSALE)) {
                            if ('all' == $recipe || 'all' != $recipe && $orderElement->getRecipeSubscription()->getRecipeDate()->getRecipe()->getSlug()) {
                                if (('all' == $recipe || ('all' != $recipe && $recipe == $orderElement->getRecipeSubscription()->getRecipeDate()->getRecipe()->getSlug())) && ('all' == $recipedate || ('all' != $recipedate && $recipedate == $orderElement->getRecipeSubscription()->getRecipeDate()->getReference())) && ('all' == $recipesubscription || ('all' != $recipesubscription && $recipesubscription == $orderElement->getRecipeSubscription()->getReference()))) {
                                    $sheet->setCellValue('A'.$i, $orderElement->getOrder()->getReference());
                                    $sheet->setCellValue('B'.$i, $orderElement->getOrder()->stringifyStatus());
                                    $sheet->setCellValue('C'.$i, date_format($orderElement->getOrder()->getCreatedAt(), $this->getParameter('date_format_simple')));
                                    $sheet->setCellValue('D'.$i, $orderElement->getRecipesubscription()->getRecipeDate()->getRecipe()->getRestaurant()->getName().' > '.$orderElement->getRecipeSubscription()->getRecipeDate()->getRecipe()->getTitle().' > '.date_format($orderElement->getRecipeSubscription()->getRecipeDate()->getStartdate(), $this->getParameter('date_format_simple')).' > '.$orderElement->getRecipeSubscription()->getName());
                                    $sheet->setCellValue('E'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getFirstname() : $orderElement->getOrder()->getUser()->getFirstname());
                                    $sheet->setCellValue('F'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getLastname() : $orderElement->getOrder()->getUser()->getFirstname());
                                    $sheet->setCellValue('G'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getClientEmail() : $orderElement->getOrder()->getUser()->getEmail());
                                    $sheet->setCellValue('H'.$i, $orderElement->getQuantity());
                                    $sheet->setCellValue('I'.$i, $orderElement->getPrice());
                                    $sheet->setCellValue('J'.$i, $orderElement->getOrder()->getPaymentgateway() ? $orderElement->getOrder()->getPaymentgateway()->getName() : '');
                                    $sheet->setCellValue('K'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getStreet() : $orderElement->getOrder()->getUser()->getStreet());
                                    $sheet->setCellValue('L'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getStreet2() : $orderElement->getOrder()->getUser()->getStreet2());
                                    $sheet->setCellValue('M'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getCity() : $orderElement->getOrder()->getUser()->getCity());
                                    $sheet->setCellValue('N'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getState() : $orderElement->getOrder()->getUser()->getState());
                                    $sheet->setCellValue('O'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getPostalcode() : $orderElement->getOrder()->getUser()->getPostalcode());
                                    $sheet->setCellValue('P'.$i, $orderElement->getOrder()->getPayment() ? $orderElement->getOrder()->getPayment()->getCountry() : ($orderElement->getOrder()->getUser()->getCountry() ? $orderElement->getOrder()->getUser()->getCountry()->getName() : ''));
                                    $sheet->setCellValue('Q'.$i, 1 == $order->getStatus() ? $orderElement->getScannedSubscriptionsCount().' / '.$orderElement->getQuantity() : '');
                                    if (1 == $order->getStatus()) {
                                        $totalSales += $orderElement->getPrice();
                                        $totalAttendees += $orderElement->getQuantity();
                                    }
                                    ++$i;
                                }
                            }
                        }
                    }
                }

                $sheet->setCellValue('A1', $this->translator->trans('Generation date').': '.date_format(new \DateTime(), $this->getParameter('date_format_simple')));
                $sheet->setCellValue('B1', $this->translator->trans('Total sales').': '.$totalSales.' '.$this->settingService->getSettings('currency_ccy'));
                $sheet->setCellValue('C1', $this->translator->trans('Total orders').': '.count($orders));
                $sheet->setCellValue('D1', $this->translator->trans('Total attendees').': '.$totalAttendees);

                if ('1' == $request->query->get('excel')) {
                    $writer = new Xlsx($spreadsheet);
                } elseif ('1' == $request->query->get('csv')) {
                    $writer = new Csv($spreadsheet);
                }
                $writer->save($temp_file);

                return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
            } elseif ('1' == $request->query->get('pdf')) {
                if (!$request->query->get('recipe')) {
                    $this->addFlash('danger', $this->translator->trans('You must choose an recipe in order to export the attendees list'));

                    return $this->settingService->redirectToReferer('orders');
                }

                if ('1' != $request->query->get('status')) {
                    $this->addFlash('danger', $this->translator->trans('You must set the status to paid in order to export the attendees list'));

                    return $this->settingService->redirectToReferer('orders');
                }

                $restaurant = 'all';
                if ($authChecker->isGranted(HasRoles::RESTAURANT)) {
                    $restaurant = $this->getUser()->getRestaurant()->getSlug();
                }

                /** @var Recipe $recipe */
                $recipe = $this->settingService->getRecipes(['slug' => $request->query->get('recipe'), 'isOnline' => 'all', 'elapsed' => 'all', 'restaurant' => $restaurant])->getQuery()->getOneOrNullResult();
                if (!$recipe) {
                    $this->addFlash('danger', $this->translator->trans('The recipe can not be found'));

                    return $this->settingService->redirectToReferer('orders');
                }

                $pdfOptions = new Options();
                // $pdfOptions->set('defaultFont', 'Arial');
                $dompdf = new Dompdf($pdfOptions);
                $html = $this->renderView('dashboard/shared/order/creators-pdf.html.twig', [
                    'recipe' => $recipe,
                    'orders' => $orders,
                ]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $dompdf->stream($recipe->getTitle().': '.$this->translator->trans('Creators list'), [
                    'Attachment' => false,
                ]);
            }
        }

        $ordersPagination = $paginator->paginate($ordersQuery, $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/shared/order/orders.html.twig', [
            'orders' => $ordersPagination,
        ]);
    }

    #[Route(path: '/%website_dashboard_path%/creator/my-subscriptions/{reference}', name: 'dashboard_creator_order_details', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/pointofsale/my-orders/{reference}', name: 'dashboard_pointofsale_order_details', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/admin/manage-orders/{reference}', name: 'dashboard_admin_order_details', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/restaurant/recent-orders/{reference}', name: 'dashboard_restaurant_order_details', methods: ['GET'])]
    /*
    public function details(Request $request, string $reference): Response
    {
        /** @var Order $order /
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        $status = null;

        if (1 == $order->getStatus()) {
            if ('flutterwave' == $order->getPaymentGateway()->getGatewayName() || 'mercadopago' == $order->getPaymentGateway()->getGatewayName()) {
                $status['value'] = 'Captured';
            } else {
                $gateway = $this->get('payum')->getGateway($order->getPaymentGateway()->getGatewayName());
                $gateway->execute($status = new GetHumanStatus($order->getPayment()));
            }
        }

        return $this->render('dashboard/shared/order/details.html.twig', compact('order', 'status'));
    }
    */

    #[Route(path: '/%website_dashboard_path%/admin/manage-orders/{reference}/cancel', name: 'dashboard_admin_order_cancel', methods: ['GET'])]
    public function cancel(Request $request, string $reference): RedirectResponse
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        if ($order->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('The order has been soft deleted, restore it before canceling it'));

            return $this->settingService->redirectToReferer('orders');
        }

        if (0 != $order->getStatus() && 1 != $order->getStatus()) {
            $this->addFlash('danger', $this->translator->trans('The order status must be paid or awaiting payment'));

            return $this->settingService->redirectToReferer('orders');
        }

        $this->settingService->handleCanceledPayment($order->getReference(), $request->query->get('note'));

        $this->addFlash('danger', $this->translator->trans('The order has been permanently canceled'));

        return $this->settingService->redirectToReferer('orders');
    }

    #[Route(path: '/%website_dashboard_path%/admin/manage-orders/{reference}/delete', name: 'dashboard_admin_order_delete', methods: ['GET'])]
    public function delete(Request $request, string $reference): RedirectResponse
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        if ($order->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted permanently successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        if ($order->getPayment()) {
            $order->getPayment()->setOrder(null);
            $this->em->persist($order);
            $this->em->persist($order->getPayment());
            $this->em->flush();
        }

        $this->em->remove($order);
        $this->em->flush();

        if ('1' == $request->query->get('forceRedirect')) {
            return $this->redirectToRoute('dashboard_admin_orders');
        }

        return $this->settingService->redirectToReferer('orders');
    }

    #[Route(path: '/%website_dashboard_path%/admin/manage-orders/{reference}/restore', name: 'dashboard_admin_order_restore', methods: ['GET'])]
    public function restore(string $reference): RedirectResponse
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        $order->setDeletedAt(null);

        foreach ($order->getOrderelements() as $orderelement) {
            $orderelement->setDeletedAt(null);
            foreach ($orderelement->getSubscriptions() as $subscription) {
                $subscription->setDeletedAt(null);
            }
            foreach ($orderelement->getSubscriptionReservations() as $subscriptionReservation) {
                $subscriptionReservation->setDeletedAt(null);
            }
        }

        $this->em->persist($order);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->settingService->redirectToReferer('orders');
    }

    #[Route(path: '/print-subscriptions/{reference}', name: 'dashboard_subscriptions_print', methods: ['GET'])]
    public function printSubscriptions(Request $request, string $reference): RedirectResponse
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference])->getQuery()->getOneOrNullResult();
        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->redirectToRoute('dashboard_creator_orders');
        }

        if ('ar' == $request->getLocale()) {
            return $this->redirectToRoute('dashboard_subscriptions_print', ['reference' => $reference, '_locale' => 'en']);
        }

        $recipeDateSubscriptionReference = $request->query->get('recipe', 'all');

        $pdfOptions = new Options();
        $pdfOptions->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('dashboard/shared/order/subscription-pdf.html.twig', [
            'order' => $order,
            'recipeDateSubscriptionReference' => $recipeDateSubscriptionReference,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($order->getReference().'-'.$this->translator->trans('subscriptions'), [
            'Attachment' => false,
        ]);
        exit(0);
    }

    #[Route(path: '/%website_dashboard_path%/restaurant/recent-orders/{reference}/resend-confirmation-email', name: 'dashboard_restaurant_order_resend_confirmation_email', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/admin/manage-orders/{reference}/resend-confirmation-email', name: 'dashboard_admin_order_resend_confirmation_email', methods: ['GET'])]
    public function resendConfirmationEmail(Request $request, string $reference)
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 0])->getQuery()->getOneOrNullResult();
        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        $this->settingService->sendOrderConfirmationEmail($order, $request->query->get('email'));
        $this->addFlash('success', $this->translator->trans('The confirmation email has been resent to').' '.$request->query->get('email'));

        return $this->settingService->redirectToReferer('orders');
    }

    #[Route(path: '/%website_dashboard_path%/creator/my-subscriptions/{reference}/contact-restaurant', name: 'dashboard_creator_order_contactRestaurant', methods: ['GET'])]
    public function contactRestaurant(Request $request, string $reference)
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        $message = $request->request->get('message');

        $emailTo = [];

        foreach ($order->getOrderelements() as $orderElement) {
            $emailTo[] = $orderElement->getRecipeSubscription()->getRecipeDate()->getRecipe()->getRestaurant()->getUser()->getEmail();
        }

        $email = (new TemplatedEmail())
            ->to(new Address($emailTo))
            ->from(new Address(
                $this->settingService->getSettings('website_no_reply_email'),
                $this->settingService->getSettings('website_name')
            ))
            ->subject($this->settingService->getSettings('website_name').' - '.$this->translator->trans('New message regarding the order').' #'.$reference)
            ->htmlTemplate('dashboard/shared/order/contact-restaurant-email.html.twig')
            ->context(
                [
                    'order' => $order,
                    'message' => $message,
                ]
            )
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $transport) {
            throw $transport;
        }

        $this->addFlash('success', $this->translator->trans('Your message has been successfully sent'));

        return $this->settingService->redirectToReferer('orders');
    }

    #[Route(path: '/%website_dashboard_path%/restaurant/recent-orders/{reference}/contact-creator', name: 'dashboard_restaurant_order_contactCreator', methods: ['GET'])]
    public function contactCreator(Request $request, string $reference)
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        $message = $request->request->get('message');

        $email = (new TemplatedEmail())
            ->to(new Address($order->getUser()->getEmail()))
            ->from(new Address(
                $this->settingService->getSettings('website_no_reply_email'),
                $this->settingService->getSettings('website_name')
            ))
            ->subject($this->settingService->getSettings('website_name').' - '.$this->translator->trans('New message regarding the order').' #'.$reference)
            ->htmlTemplate('dashboard/shared/order/contact-creator-email.html.twig')
            ->context(
                [
                    'order' => $order,
                    'message' => $message,
                    'restaurant' => $this->getUser()->getRestaurant(),
                ]
            )
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $transport) {
            throw $transport;
        }

        $this->addFlash('success', $this->translator->trans('Your message has been successfully sent'));

        return $this->settingService->redirectToReferer('orders');
    }

    #[Route(path: '/%website_dashboard_path%/restaurant/recent-orders/{reference}/validate', name: 'dashboard_restaurant_order_validate', methods: ['GET'])]
    #[Route(path: '/%website_dashboard_path%/admin/manage-orders/{reference}/validate', name: 'dashboard_admin_order_validate', methods: ['GET'])]
    public function validate(string $reference): RedirectResponse
    {
        /** @var Order $order */
        $order = $this->settingService->getOrders(['reference' => $reference, 'status' => 0])->getQuery()->getOneOrNullResult();

        if (!$order) {
            $this->addFlash('danger', $this->translator->trans('The order can not be found'));

            return $this->settingService->redirectToReferer('orders');
        }

        if ($order->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('The order has been soft deleted, restore it before canceling it'));

            return $this->settingService->redirectToReferer('orders');
        }

        $this->settingService->handleSuccessfulPayment($order->getReference());

        $this->addFlash('success', $this->translator->trans('The order has been successfully validated'));

        return $this->settingService->redirectToReferer('orders');
    }
}
