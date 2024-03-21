<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AttachmentExistValidator extends ConstraintValidator
{
    /**
     * @param AttachmentExist $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AttachmentNoExist) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ id }}', (string) $value->getId())
            ->addViolation()
        ;
    }
}
