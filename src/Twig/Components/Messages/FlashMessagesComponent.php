<?php

declare(strict_types=1);

namespace App\Twig\Components\Messages;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('flash_messages', template: 'components/messages/flash_messages.html.twig')]
final class FlashMessagesComponent
{
}
