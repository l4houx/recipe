<?php

namespace App\Event;

use App\DTO\HelpCenterSupportDTO;
use Symfony\Contracts\EventDispatcher\Event;

class HelpCenterSupportRequestEvent extends Event
{
    public function __construct(
        public readonly HelpCenterSupportDTO $data
    ) {
    }
}