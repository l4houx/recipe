<?php

namespace App\Infrastructural\Messenger\Message;

final class RecipePDFMessage
{
    public function __construct(public readonly int $id)
    {
        # code...
    }
}
