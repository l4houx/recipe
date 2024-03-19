<?php

namespace App\Infrastructural\Payment\Stripe;

use App\Entity\Pricing;
use App\Entity\User;
use Stripe\BalanceTransaction;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Plan as StripePlan;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Subscription;

class StripeApi
{
    private readonly StripeClient $stripe;
    private array $taxes = [];

    public function __construct(
        private string $privateKey = ''
    ) {
        Stripe::setApiVersion('2020-08-27');

        $this->taxes = ['txr_1HfQaHFCMNgisvowjXXZAA7z'];

        if (str_contains($privateKey, 'live')) {
            $this->taxes = ['txr_1I7c7DFCMNgisvowdAol5zkl'];
        }

        $this->stripe = new StripeClient($privateKey);
    }

    /**
     * Create a customer stripe and save the user ID.
     */
    public function createCustomer(User $user): User
    {
        if ($user->getStripeId()) {
            return $user;
        }

        $client = $this->stripe->customers->create([
            'metadata' => [
                'user_id' => (string) $user->getId(),
            ],
            'email' => $user->getEmail(),
            'name' => $user->getUsername(),
        ]);

        $user->setStripeId($client->id);

        return $user;
    }

    public function getCustomer(string $customerId): Customer
    {
        return $this->stripe->customers->retrieve($customerId);
    }

    public function getInvoice(string $invoice): Invoice
    {
        return $this->stripe->invoices->retrieve($invoice);
    }

    public function getSubscription(string $subscriptionId): Subscription
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId);
    }

    public function getPaymentIntent(string $id): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($id);
    }

    /**
     * Creates a session and returns the payment URL.
     */
    public function createSuscriptionSession(User $user, Pricing $pricing, string $url): string
    {
        $session = $this->stripe->checkout->sessions->create([
            'cancel_url' => $url,
            'success_url' => $url.'?success=1',
            'mode' => 'subscription',
            'payment_method_types' => [
                'card',
            ],
            'subscription_data' => [
                'metadata' => [
                    'pricing_id' => $pricing->getId(),
                ],
            ],
            'customer' => $user->getStripeId(),
            'line_items' => [
                [
                    'price' => $pricing->getStripeId(),
                    'quantity' => 1,
                    'dynamic_tax_rates' => $this->taxes,
                ],
            ],
        ]);

        return $session->id;
    }

    public function createPaymentSession(User $user, Pricing $pricing, string $url): string
    {
        $session = $this->stripe->checkout->sessions->create([
            'cancel_url' => $url,
            'success_url' => $url.'?success=1',
            'mode' => 'payment',
            'payment_method_types' => [
                'card',
            ],
            'customer' => $user->getStripeId(),
            'metadata' => [
                'pricing_id' => $pricing->getId(),
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'pricing_id' => $pricing->getId(),
                ],
            ],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $pricing->getTitle(),
                            'images' => ['https://www.grafikart.fr/assets/logo-footer-f0a333a1c7b2c1833354864ad7c405e0396d894732bf03abb902f2281e6c942e.png'],
                        ],
                        'unit_amount' => $pricing->getPrice() * 100,
                    ],
                    'quantity' => 1,
                    'dynamic_tax_rates' => $this->taxes,
                ],
            ],
        ]);

        return $session->id;
    }

    /**
     * Returns the URL of the stripe subscription profile.
     */
    public function getBillingUrl(User $user, string $returnUrl): string
    {
        return $this->stripe->billingPortal->sessions->create([
            'customer' => $user->getStripeId(),
            'return_url' => $returnUrl,
        ])->url;
    }

    public function getPlan(string $id): StripePlan
    {
        return $this->stripe->plans->retrieve($id);
    }

    public function getCheckoutSessionFromIntent(string $paymentIntent): Session
    {
        /** @var Session[] $sessions */
        $sessions = $this->stripe->checkout->sessions->all(['payment_intent' => $paymentIntent])->data;

        return $sessions[0];
    }

    public function getTransaction(string $id): BalanceTransaction
    {
        return $this->stripe->balanceTransactions->retrieve($id);
    }
}
