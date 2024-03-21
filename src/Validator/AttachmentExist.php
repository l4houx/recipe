<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class AttachmentExist extends Constraint
{
    public string $message = 'No attachment matches id {{ id }}';
}
