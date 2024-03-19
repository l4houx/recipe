<?php

namespace App\Infrastructural\Payment\Event;

use App\Entity\User;
use App\Infrastructural\Payment\Payment;

class PaymentEvent
{
    public function __construct(private readonly Payment $payment, private readonly User $user)
    {
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
