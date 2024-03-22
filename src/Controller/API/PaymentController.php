<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\Pricing;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Infrastructural\Payment\Event\PaymentEvent;
use App\Infrastructural\Payment\Exception\PaymentFailedException;
use App\Infrastructural\Payment\Paypal\PaypalService;
use App\Infrastructural\Payment\Stripe\StripeApi;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @method User getUser()
 */
#[IsGranted(HasRoles::DEFAULT)]
class PaymentController extends BaseController
{
    #[Route(path: '/payment/paypal/{orderId}', name: 'payment_paypal', methods: ['POST'])]
    public function paypal(
        string $orderId,
        TranslatorInterface $translator,
        PaypalService $paypal,
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        try {
            $payment = $paypal->createPayment($orderId);
            $payment = $paypal->capture($payment);
            $dispatcher->dispatch(new PaymentEvent($payment, $this->getUser()));

            return $this->json([]);
        } catch (PaymentFailedException $e) {
            return $this->json(['title' => $translator->trans('Error during payment'), 'detail' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route(path: '/payment/{id<\d+>}/stripe/checkout', name: 'payment_stripe_checkout', methods: ['POST'])]
    public function stripe(
        Request $request,
        TranslatorInterface $translator,
        Pricing $pricing,
        StripeApi $api,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $isSubscription = '1' === $request->get('subscription');
        $url = $urlGenerator->generate('pricing', [], UrlGeneratorInterface::ABSOLUTE_URL);
        try {
            $api->createCustomer($this->getUser());
            $em->flush();

            return $this->json([
                'id' => $isSubscription ? $api->createSuscriptionSession($this->getUser(), $pricing, $url) : $api->createPaymentSession($this->getUser(), $pricing, $url),
            ]);
        } catch (\Exception) {
            return $this->json(['title' => $translator->trans('Unable to contact Stripe API')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
