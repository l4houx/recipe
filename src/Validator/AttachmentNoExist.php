<?php

namespace App\Validator;

use App\Entity\Attachment;

/**
 * Represents an attachment that does not exist in the database.
 */
class AttachmentNoExist extends Attachment
{
    public function __construct(int $expectedId)
    {
        $this->id = $expectedId;
    }
}
