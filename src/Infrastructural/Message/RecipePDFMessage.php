<?php

namespace App\Infrastructural\Message;

final class RecipePDFMessage
{
    public function __construct(public readonly int $id)
    {
        # code...
    }
}
