<?php

namespace App\Infrastructural\Payment\Paypal;

use App\Infrastructural\Payment\Payment;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructural\Payment\Exception\PaymentFailedException;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;

class PaypalService
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PayPalHttpClient $client
    ) {
    }

    /**
     * We create a payment from the order ID.
     */
    public function createPayment(string $orderId): Payment
    {
        try {
            // We retrieve the order information
            /** @var \stdClass $order */
            $order = $this->client->execute(new OrdersGetRequest($orderId))->result;

            // We standardize payment
            $payment = new Payment();
            $unit = $order->purchase_units[0];
            $payment->id = $order->id;
            $payment->planId = (int) $unit->custom_id;
            $payment->firstname = $order->payer->name->given_name ?: '';
            $payment->lastname = $order->payer->name->surname ?: '';
            $payment->address = $unit->shipping->address->address_line_1 ?: '';
            $payment->city = $unit->shipping->address->admin_area_2 ?: '';
            $payment->postalCode = $unit->shipping->address->postal_code ?: '';
            $payment->countryCode = $unit->shipping->address->country_code ?: '';
            $payment->amount = floatval($unit->amount->value);
            $payment->vat = floatval($unit->amount->breakdown->tax_total->value);

            return $payment;
        } catch (HttpException $e) {
            throw PaymentFailedException::fromPaypalHttpException($e);
        }
    }

    /**
     * Launches the “capture” of the payment.
     */
    public function capture(Payment $payment): Payment
    {
        try {
            /** @var \stdClass $capture */
            $capture = $this->client->execute(new OrdersCaptureRequest($payment->id))->result;

            if ('COMPLETED' === $capture->status) {
                $capture = $capture->purchase_units[0]->payments->captures[0];
                $payment->id = $capture->id;
                $payment->fee = $capture->seller_receivable_breakdown->paypal_fee->value;

                return $payment;
            }
            throw new PaymentFailedException($this->translator->trans('Unable to capture this payment'));
        } catch (HttpException $e) {
            throw PaymentFailedException::fromPaypalHttpException($e);
        }
    }
}
