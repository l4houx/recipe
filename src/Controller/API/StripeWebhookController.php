<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\Pricing;
use App\Entity\Subscription;
use App\Entity\User;
use App\Infrastructural\Payment\Event\PaymentEvent;
use App\Infrastructural\Payment\Event\PaymentRefundedEvent;
use App\Infrastructural\Payment\Payment;
use App\Infrastructural\Payment\Stripe\StripePaymentFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stripe\Charge;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\Subscription as StripeSubscription;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class StripeWebhookController extends BaseController
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly StripePaymentFactory $paymentFactory
    ) {
    }

    #[Route(path: '/stripe/webhook', name: 'stripe_webhook')]
    public function index(Event $event): JsonResponse
    {
        return match ($event->type) {
            'payment_intent.succeeded' => $this->onPaymentIntentSucceeded($event->data['object']),
            'charge.refunded' => $this->onRefund($event->data['object']),
            'customer.subscription.created' => $this->onSubscriptionCreated($event->data['object']),
            'customer.subscription.updated' => $this->onSubscriptionUpdated($event->data['object']),
            'customer.subscription.deleted' => $this->onSubscriptionDeleted($event->data['object']),
            default => $this->json([]),
        };
    }

    public function onPaymentIntentSucceeded(PaymentIntent $intent): JsonResponse
    {
        $user = $this->getUserFromCustomer((string) $intent->customer);
        $payment = $this->paymentFactory->createPaymentFromIntent($intent);
        $this->dispatcher->dispatch(new PaymentEvent($payment, $user));

        return new JsonResponse([]);
    }

    public function onRefund(Charge $charge): JsonResponse
    {
        $payment = new Payment();
        $payment->id = (string) $charge->payment_intent;
        $this->dispatcher->dispatch(new PaymentRefundedEvent($payment));

        return $this->json([]);
    }

    public function onSubscriptionCreated(StripeSubscription $stripeSubscription): JsonResponse
    {
        $pricing = $this->em->getRepository(Pricing::class)->find($stripeSubscription->metadata['pricing_id']);
        if (null === $pricing) {
            throw new NoResultException();
        }

        $subscription = (new Subscription())
            ->setState(Subscription::ACTIVE)
            ->setNextPayment(new \DateTimeImmutable("@{$stripeSubscription->current_period_end}"))
            ->setPricing($pricing)
            ->setUser($this->getUserFromCustomer((string) $stripeSubscription->customer))
            ->setCreatedAt(new \DateTimeImmutable())
            ->setStripeId($stripeSubscription->id)
        ;

        $this->em->persist($subscription);
        $this->em->flush();

        return new JsonResponse([]);
    }

    private function onSubscriptionUpdated(StripeSubscription $stripeSubscription): JsonResponse
    {
        $subscription = $this->em->getRepository(Subscription::class)->findOneBy(['stripeId' => $stripeSubscription->id]);
        if (!($subscription instanceof Subscription)) {
            throw new \Exception($this->translator->trans('Unable to find matching subscription'));
        }

        if ($stripeSubscription->cancel_at_period_end) {
            $subscription->setState(Subscription::INACTIVE);
        } else {
            $subscription->setState(Subscription::ACTIVE);
            $subscription->setNextPayment(new \DateTimeImmutable("@{$stripeSubscription->current_period_end}"));
        }

        $this->em->flush();

        return new JsonResponse([]);
    }

    private function onSubscriptionDeleted(StripeSubscription $stripeSubscription): JsonResponse
    {
        $subscription = $this->em->getRepository(Subscription::class)->findOneBy(['stripeId' => $stripeSubscription->id]);
        if (!($subscription instanceof Subscription)) {
            throw new \Exception($this->translator->trans('Unable to find matching subscription'));
        }

        $this->em->remove($subscription);
        $this->em->flush();

        return new JsonResponse([]);
    }

    private function getUserFromCustomer(string $customerId): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['stripeId' => $customerId]);
        if (null === $user) {
            throw new \Exception($this->translator->trans('Unable to find the user corresponding to the payment'));
        }

        return $user;
    }
}
