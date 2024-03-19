<?php

namespace App\Infrastructural\Payment\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class StripePlan extends Constraint
{
    public string $message = 'The formula "{{ value }}" does not exist on stripe.';
}
