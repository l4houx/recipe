<?php

namespace App\Infrastructural\Payment\Event;

use App\Infrastructural\Payment\Payment;

class PaymentRefundedEvent
{
    public function __construct(private readonly Payment $payment)
    {
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }
}
