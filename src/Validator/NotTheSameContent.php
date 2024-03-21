<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to ensure that a revision contains at least one modification.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class NotTheSameContent extends Constraint
{
    public string $message = 'The revise must have at least one modification from the original article';

    public function getTargets(): string
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
