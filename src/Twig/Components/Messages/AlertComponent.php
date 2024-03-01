<?php

declare(strict_types=1);

namespace App\Twig\Components\Messages;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('alert', template: 'components/messages/alert.html.twig')]
final class AlertComponent
{
    public string $type;

    public function getIcon(): string
    {
        return match ($this->type) {
            'success' => 'check-circle',
            'danger' => 'slash-circle',
            'warning' => 'exclamation-circle',
            default => 'info-circle',
        };
    }
}
