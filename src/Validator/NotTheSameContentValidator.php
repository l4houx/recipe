<?php

namespace App\Validator;

use App\Entity\Revise;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NotTheSameContentValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotTheSameContent) {
            throw new UnexpectedTypeException($constraint, NotTheSameContent::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!$value instanceof Revise) {
            throw new UnexpectedValueException($value, Revise::class);
        }

        if (null !== $value->getTarget() && $value->getContent() === $value->getTarget()->getContent()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
