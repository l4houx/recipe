<?php

declare(strict_types=1);

namespace App\Infrastructural\Payment\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Add methods to retrieve public information for payments.
 */
class TwigPaymentExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $stripePublicKey = '',
        private readonly string $paypalClientId = ''
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('stripeKey', $this->getStripePublicKey(...)),
            new TwigFunction('paypalClientId', $this->getPaypalClientId(...)),
        ];
    }

    public function getStripePublicKey(): string
    {
        return $this->stripePublicKey;
    }

    public function getPaypalClientId(): string
    {
        return $this->paypalClientId;
    }
}
