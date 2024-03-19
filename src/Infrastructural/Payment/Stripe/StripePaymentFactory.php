<?php

namespace App\Infrastructural\Payment\Stripe;

use Stripe\Charge;
use Stripe\PaymentIntent;

class StripePaymentFactory
{
    public function __construct(private readonly StripeApi $api)
    {
    }

    public function createPaymentFromIntent(PaymentIntent $intent): StripePayment
    {
        /** @var Charge $charge */
        $charge = $intent->charges->data[0];

        if (is_string($charge->balance_transaction)) {
            $charge->balance_transaction = $this->api->getTransaction($charge->balance_transaction);
        }

        // Payment comes from a subscription and has an invoice
        if ($intent->invoice) {
            $invoice = $this->api->getInvoice($intent->invoice);
            $subscription = $this->api->getSubscription((string) $invoice->subscription);
            $intent->metadata = $subscription->metadata;

            return new StripePayment($intent, $invoice);
        }

        // Payment comes from a checkout session
        $session = $this->api->getCheckoutSessionFromIntent($intent->id);

        return new StripePayment($intent, $session);
    }
}
