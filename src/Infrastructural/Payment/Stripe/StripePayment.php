<?php

namespace App\Infrastructural\Payment\Stripe;

use App\Infrastructural\Payment\Payment;
use Stripe\BalanceTransaction;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\InvoiceLineItem;
use Stripe\PaymentIntent;

class StripePayment extends Payment
{
    /**
     * @param Session|Invoice $extra Additional data necessary for price recovery
     */
    public function __construct(PaymentIntent $intent, object $extra)
    {
        /** @var Charge $charge */
        $charge = $intent->charges->data[0];
        $this->id = $intent->id;
        $this->planId = $intent->metadata['plan_id'];
        $this->firstname = $charge->billing_details['name'] ?: '';
        $this->lastname = '';
        $this->address = $charge->billing_details['address']['line1']."\n".$charge->billing_details['address']['line2'];
        $this->city = $charge->billing_details['address']['city'] ?: '';
        $this->postalCode = $charge->billing_details['address']['postal_code'] ?: '';
        $this->countryCode = $charge->billing_details['address']['country'] ?: '';
        /** @var BalanceTransaction $transaction */
        $transaction = $charge->balance_transaction;
        $this->fee = $transaction->fee / 100;

        // Payment linked to an invoice
        if ($extra instanceof Invoice) {
            /** @var InvoiceLineItem $line */
            $line = $extra->lines->data[0];
            $this->amount = $line->amount / 100;
            if (isset($line->tax_amounts[0])) {
                /** @var \stdClass $tax */
                $tax = $line->tax_amounts[0];
                $this->vat = $tax->amount / 100;
            }
        } elseif ($extra instanceof Session) {
            $this->amount = $extra->amount_subtotal / 100;
            $this->vat = ($extra->total_details['amount_tax'] ?? 0) / 100;
        }
    }
}
