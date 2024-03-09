<?php

namespace App\Event;

use App\DTO\ContactFormDTO;
use Symfony\Contracts\EventDispatcher\Event;

class ContactRequestEvent extends Event
{
    public function __construct(
        public readonly ContactFormDTO $data
    ) {
    }
}