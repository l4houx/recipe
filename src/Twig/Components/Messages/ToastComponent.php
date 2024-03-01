<?php

declare(strict_types=1);

namespace App\Twig\Components\Messages;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('toast', template: 'components/messages/toast.html.twig')]
final class ToastComponent
{
    public string $message;
    public string $type;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function getTitle(): string
    {
        return match ($this->type) {
            'success' => $this->translator->trans('Success !'),
            'danger' => $this->translator->trans('Error !'),
            'warning' => $this->translator->trans('Warning !'),
            default => $this->translator->trans('Info !'),
        };
    }

    public function getColor(): string
    {
        return match ($this->type) {
            'success' => 'success',
            'danger' => 'danger',
            'warning' => 'warning',
            default => 'info',
        };
    }

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
