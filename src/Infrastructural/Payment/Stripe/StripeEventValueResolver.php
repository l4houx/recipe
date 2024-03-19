<?php

namespace App\Infrastructural\Payment\Stripe;

use Stripe\Event;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Injects a stripe event into the actions of a controller by validating the signature.
 */
class StripeEventValueResolver implements ValueResolverInterface
{
    public function __construct(private readonly string $webhookSecret = '')
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (Event::class !== $argument->getType()) {
            return [];
        }
        yield Webhook::constructEvent(
            $request->getContent(false),
            (string) $request->headers->get('stripe-signature'),
            $this->webhookSecret
        );
    }
}
