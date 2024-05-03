<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\PayoutRequest;
use App\Entity\RecipeDate;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/admin/manage-payout-requests')]
#[Route(path: '/%website_dashboard_path%/restaurant/my-payout-requests')]
#[IsGranted(HasRoles::DEFAULT)]
class PayoutRequestController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/new/{recipeDateReference}', name: 'dashboard_restaurant_recipe_date_request_payout', methods: ['GET', 'POST'], requirements: ['recipeDateReference' => Requirement::ASCII_SLUG])]
    public function payoutRequestNew(Request $request, string $recipeDateReference): Response
    {
        $restaurantPayoutMethods = $this->settingService->getPaymentGateways(['restaurant' => $this->getUser()->getRestaurant()->getSlug()])->getQuery()->getResult();
        if (0 == count($restaurantPayoutMethods)) {
            $this->addFlash('danger', $this->translator->trans('Please set a payout method before submitting a payout request'));

            return $this->redirectToRoute('dashboard_restaurant_setting_payouts', [], Response::HTTP_SEE_OTHER);
        }

        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['reference' => $recipeDateReference, 'restaurant' => $this->getUser()->getRestaurant()->getSlug()])->getQuery()->getOneOrNullResult();

        if (!$recipeDate) {
            $this->addFlash('danger', $this->translator->trans('The recipe date can not be found'));

            return $this->redirectToRoute('recipe', [], Response::HTTP_SEE_OTHER);
        }

        if ($recipeDate->isFree()) {
            $this->addFlash('danger', $this->translator->trans('A payout can not be requested on a free recipe date'));

            return $this->redirectToRoute('recipe', [], Response::HTTP_SEE_OTHER);
        }

        if ($recipeDate->getRestaurantPayoutAmount() <= 0) {
            $this->addFlash('danger', $this->translator->trans('The restaurant revenue from this recipe date is currently zero'));

            return $this->redirectToRoute('recipe', [], Response::HTTP_SEE_OTHER);
        }

        if ($recipeDate->payoutRequested()) {
            $this->addFlash('danger', $this->translator->trans('A payout is already requested for this recipe date'));

            return $this->redirectToRoute('recipe', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->isMethod('POST')) {
            $payoutMethodSlug = $request->request->get('payout_method');

            $payoutMethod = $this->settingService->getPaymentGateways(['restaurant' => $this->getUser()->getRestaurant()->getSlug(), 'slug' => $payoutMethodSlug])->getQuery()->getOneOrNullResult();
            if (!$payoutMethod) {
                $this->addFlash('danger', $this->translator->trans('The payout method can not be found'));

                return $this->redirectToRoute('dashboard_restaurant_setting_payouts', [], Response::HTTP_SEE_OTHER);
            }

            $payoutRequest = (new PayoutRequest());
            $payoutRequest->setRestaurant($this->getUser()->getRestaurant());
            $payoutRequest->setPaymentGateway($payoutMethod);
            $payoutRequest->setRecipeDate($recipeDate);
            $payoutRequest->setStatus(0);

            $this->em->persist($payoutRequest);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('The payout request has been successfully submitted, you will be notified by email once it is processed'));

            return $this->redirectToRoute('dashboard_restaurant_payout_requests', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->render('dashboard/shared/payout/request.html.twig', compact('recipeDate', 'restaurantPayoutMethods'));
        }
    }

    #[Route(path: '/', name: 'dashboard_admin_payout_requests', methods: ['GET'])]
    #[Route(path: '/', name: 'dashboard_restaurant_payout_requests', methods: ['GET'])]
    public function payoutRequests(Request $request, PaginatorInterface $paginator): Response
    {
        $reference = '' == $request->query->get('reference') ? 'all' : $request->query->get('reference');
        $recipedate = '' == $request->query->get('recipedate') ? 'all' : $request->query->get('recipedate');
        $restaurant = '' == $request->query->get('restaurant') ? 'all' : $request->query->get('restaurant');
        $datefrom = '' == $request->query->get('datefrom') ? 'all' : $request->query->get('datefrom');
        $dateto = '' == $request->query->get('dateto') ? 'all' : $request->query->get('dateto');
        $status = '' == $request->query->get('status') ? 'all' : $request->query->get('status');

        $rows = $paginator->paginate($this->settingService->getPayoutRequests(['reference' => $reference, 'recipedate' => $recipedate, 'restaurant' => $restaurant, 'datefrom' => $datefrom, 'dateto' => $dateto, 'status' => $status])->getQuery(), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/shared/payout/requests.html.twig', compact('rows'));
    }

    #[Route(path: '/{reference}/cancel', name: 'dashboard_admin_payout_request_cancel', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{reference}/cancel', name: 'dashboard_restaurant_payout_request_cancel', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    public function cancel(Request $request, string $reference): Response
    {
        /** @var PayoutRequest $payoutRequest */
        $payoutRequest = $this->settingService->getPayoutRequests(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$payoutRequest) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be found'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if ($payoutRequest->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('The payout request has been soft deleted, restore it before canceling it'));

            if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
                return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('dashboard_restaurant_payout_requests', [], Response::HTTP_SEE_OTHER);
            }
        }

        if (0 != $payoutRequest->getStatus()) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be canceled because it is already processed'));

            if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
                return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('dashboard_restaurant_payout_requests', [], Response::HTTP_SEE_OTHER);
            }
        }

        $payoutRequest->setStatus(-1);

        if ($request->query->get('note')) {
            $payoutRequest->setNote($request->query->get('note'));
        }

        $this->em->persist($payoutRequest);
        $this->em->flush();

        if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
            $this->settingService->sendPayoutProcessedEmail($payoutRequest, $payoutRequest->getRestaurant()->getUser()->getEmail());
        }

        $this->addFlash('danger', $this->translator->trans('The payout request has been permanently canceled'));

        if ($this->isGranted(HasRoles::ADMINAPPLICATION)) {
            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->redirectToRoute('dashboard_restaurant_payout_requests', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route(path: '/{reference}/failed', name: 'dashboard_admin_payout_request_failed', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    public function failed(string $reference): Response
    {
        /** @var PayoutRequest $payoutRequest */
        $payoutRequest = $this->settingService->getPayoutRequests(['reference' => $reference, 'status' => 0])->getQuery()->getOneOrNullResult();
        if (!$payoutRequest) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be found'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if ($payoutRequest->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('The payout request has been soft deleted, restore it before canceling it'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if (0 != $payoutRequest->getStatus()) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be canceled because it is already processed'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        $payoutRequest->setStatus(-2);

        $this->em->persist($payoutRequest);
        $this->em->flush();

        $this->settingService->sendPayoutProcessedEmail($payoutRequest, $payoutRequest->getRestaurant()->getUser()->getEmail());

        $this->addFlash('danger', $this->translator->trans('The payout request can not be processed at this moment'));

        return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{reference}/delete', name: 'dashboard_admin_payout_request_delete', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    public function delete(Request $request, string $reference): Response
    {
        /** @var PayoutRequest $payoutRequest */
        $payoutRequest = $this->settingService->getPayoutRequests(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$payoutRequest) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be found'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if ($payoutRequest->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was permanently deleted successfully.'));
        } else {
            $this->addFlash('info', $this->translator->trans('Content was deleted successfully.'));
        }

        // $settings->sendPayoutProcessedEmail($payoutRequest, $payoutRequest->getRestaurant()->getUser()->getEmail());
        $payoutRequest->setStatus(-1);
        $payoutRequest->setNote($this->translator->trans('Automatically canceled before deletion'));

        $this->em->persist($payoutRequest);
        $this->em->flush();
        $this->em->remove($payoutRequest);
        $this->em->flush();

        if ('1' == $request->query->get('forceRedirect')) {
            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{reference}/restore', name: 'dashboard_admin_payout_request_restore', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    public function restore(string $reference): Response
    {
        /** @var PayoutRequest $payoutRequest */
        $payoutRequest = $this->settingService->getPayoutRequests(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$payoutRequest) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be found'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        $payoutRequest->setDeletedAt(null);

        $this->em->persist($payoutRequest);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{reference}/approve', name: 'dashboard_admin_payout_request_approve', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    public function approve(Request $request, string $reference): Response
    {
        /** @var PayoutRequest $payoutRequest */
        $payoutRequest = $this->settingService->getPayoutRequests(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$payoutRequest) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be found'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if (0 != $payoutRequest->getStatus()) {
            $this->addFlash('danger', $this->translator->trans('The payout request has been already processed'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        /*
        $payoutRequest->getPaymentGateway()->decrypt($this->cypher);

        if ($payoutRequest->getPaymentGateway()->getFactoryName() == "paypal_rest") {
            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                $payoutRequest->getPaymentGateway()->getConfig()['client_id'], $payoutRequest->getPaymentGateway()->getConfig()['client_secret']
                )
            );

            $mode = "sandbox";
            if ($payoutRequest->getPaymentGateway()->getConfig()['sandbox'] == false) {
                $mode = "live";
            }
            $apiContext->getConfig(array(
                'log.LogEnabled' => false,
                'mode' => $mode
            ));

            $payer = new Payer();
            $payer->setPaymentMethod('Paypal');
            $amount = new Amount();

            $amount->setTotal($payoutRequest->getRecipeDate()->getRestaurantPayoutAmount());
            $amount->setCurrency($setting->getSettings("currency_ccy"));

            $transaction = new Transaction();
            $transaction->setAmount($amount);

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($this->generateUrl("dashboard_admin_payout_request_approved", ["reference" => $payoutRequest->getReference()], UrlGeneratorInterface::ABSOLUTE_URL))
                ->setCancelUrl($this->generateUrl("dashboard_admin_payout_request_failed", ["reference" => $payoutRequest->getReference()], UrlGeneratorInterface::ABSOLUTE_URL))
            ;

            $payment = new Payment();
            $payment->setIntent("ORDER")
                ->setPayer($payer)
                ->setTransactions(array($transaction))
                ->setRedirectUrls($redirectUrls)
            ;

            try {
                $payment->create($apiContext);
                $payoutRequest->setPayment($payment->toArray());
                $em->persist($payoutRequest);
                $em->flush();
                return $this->redirect($payment->getApprovalLink());
            } catch (PayPalConnectionException $ex) {
                $this->addFlash('danger', $translator->trans('An danger has occured while processing your request'));
                $this->redirectToRoute("dashboard_admin_payout_requests");
            }
        } else if ($payoutRequest->getPaymentGateway()->getFactoryName() == "stripe_checkout") {
            if ($request->isMethod("POST")) {
                \Stripe\Stripe::setApiKey($payoutRequest->getPaymentGateway()->getConfig()['secret_key']);
                $token = $request->request->get('stripeToken');
                $stripePaymentDanger = false;

                try {
                    $charge = \Stripe\Charge::create([
                        'amount' => number_format($payoutRequest->getRecipeDate()->getRestaurantPayoutAmount(), 2, '', ''),
                        'currency' => $setting->getSettings("currency_ccy"),
                        'description' => $translator->trans("Restaurant revenue from %website_name%", ["%website_name%" => $setting->getSettings("website_name")]),
                        'source' => $token,
                    ]);
                } catch (\Stripe\Danger\Card $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (\Stripe\Danger\Api $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (\Stripe\Danger\ApiConnection $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (\Stripe\Danger\Authentication $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (\Stripe\Danger\Base $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (\Stripe\Danger\InvalidRequest $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (\Stripe\Danger\Permission $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (\Stripe\Danger\RateLimit $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request') . ": " . $e->getMessage());
                    $stripePaymentDanger = true;
                } catch (Exception $e) {
                    $this->addFlash('danger', $translator->trans('An danger has occured while processing your request'));
                    $stripePaymentDanger = true;
                }

                if ($stripePaymentDanger) {
                    return $this->redirectToRoute("dashboard_admin_payout_requests");
                }

                $payoutRequest->setPayment(json_decode($charge->getLastResponse()->body, true));
                $em->persist($payoutRequest);
                $em->flush();

                if ($charge->getLastResponse()->code == 200) {
                    $payoutRequest->setStatus(1);
                    $em->persist($payoutRequest);
                    $em->flush();
                    $setting->sendPayoutProcessedEmail($payoutRequest, $payoutRequest->getRestaurant()->getUser()->getEmail());
                    $this->addFlash('success', $translator->trans('The payout request has been successfully processed'));
                } else {
                    return $this->redirectToRoute("dashboard_admin_payout_request_failed", ["reference" => $payoutRequest->getReference()]);
                }
            } else {
                return $this->render('dashboard/shared/payout/stripe.html.twig', [
                    "stripePublishableKey" => $payoutRequest->getPaymentGateway()->getConfig()['publishable_key']
                ]);
            }
        }
        */

        return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{reference}/approved', name: 'dashboard_admin_payout_request_approved', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    public function approved(Request $request, string $reference): Response
    {
        /** @var PayoutRequest $payoutRequest */
        $payoutRequest = $this->settingService->getPayoutRequests(['reference' => $reference, 'status' => 0])->getQuery()->getOneOrNullResult();
        if (!$payoutRequest) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be found'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if ($payoutRequest->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('The payout request has been soft deleted, restore it before canceling it'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if (0 != $payoutRequest->getStatus()) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be canceled because it is already processed'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        if (null == $payoutRequest->getPayment()) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be processed at this moment'));

            return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
        }

        /*
        if ($payoutRequest->getPaymentGateway()->getFactoryName() == "paypal_rest") {
            if (!$request->query->has("paymentId") || !$request->query->has("token") || !$request->query->has("PayerID")) {
                $this->addFlash('danger', $this->translator->trans('The payout request can not be processed at this moment'));

                return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
            }

            $payment = new Payment();
            $payment->fromArray($payoutRequest->getPayment());

            $paymentExecution = new PaymentExecution();
            $paymentExecution->setPayerId($request->query->get("PayerID"));

            $transaction = $payment->getTransactions()[0];
            $paymentExecution->addTransaction($transaction);

            $payoutRequest->getPaymentGateway()->decrypt($this->cypher);
            $apiContext = new ApiContext(
                    new OAuthTokenCredential(
                    $payoutRequest->getPaymentGateway()->getConfig()['client_id'], $payoutRequest->getPaymentGateway()->getConfig()['client_secret']
                    )
            );

            $mode = "sandbox";
            if ($payoutRequest->getPaymentGateway()->getConfig()['sandbox'] == false) {
                $mode = "live";
            }
            $apiContext->getConfig(array(
                'log.LogEnabled' => false,
                'mode' => $mode
            ));

            try {
                $succesfullTransactionDetails = $payment->execute($paymentExecution, $apiContext);

                $payoutRequest->setStatus(1);
                $payoutRequest->setPayment($succesfullTransactionDetails->toArray());

                $this->em->persist($payoutRequest);
                $this->em->flush();

                $this->settingService->sendPayoutProcessedEmail($payoutRequest, $payoutRequest->getRestaurant()->getUser()->getEmail());
                $this->addFlash('success', $this->translator->trans('The payout request has been successfully processed'));
            } catch (PayPalConnectionException $ex) {
                $this->addFlash('danger', $this->translator->trans('An danger has occured while processing your request'));
                return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
            }
        }
        */

        return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{reference}/details', name: 'dashboard_admin_payout_request_details', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    #[Route(path: '/{reference}/details', name: 'dashboard_restaurant_payout_request_details', methods: ['GET'], requirements: ['reference' => Requirement::ASCII_SLUG])]
    public function details(string $reference): Response
    {
        /** @var PayoutRequest $payoutRequest */
        $payoutRequest = $this->settingService->getPayoutRequests(['reference' => $reference, 'status' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$payoutRequest) {
            $this->addFlash('danger', $this->translator->trans('The payout request can not be found'));

            if ($this->isGranted(HasRoles::RESTAURANT)) {
                return $this->redirectToRoute('dashboard_restaurant_payout_requests', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('dashboard_admin_payout_requests', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('dashboard/shared/payout/details.html.twig', compact('payoutRequest'));
    }
}
