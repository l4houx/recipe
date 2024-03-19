<?php

namespace App\Infrastructural\Messenger\MessageHandler;

use App\Infrastructural\Mail\Mail;
use App\Infrastructural\Messenger\Message\ServiceMethodMessage;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

#[AsMessageHandler]
final class ServiceMethodMessageHandler implements ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function __invoke(ServiceMethodMessage $message): void
    {
        /** @var callable $callable */
        $callable = [
            $this->container->get($message->getServiceName()),
            $message->getMethod(),
        ];

        call_user_func_array($callable, $message->getParams());
    }

    public static function getSubscribedServices(): array
    {
        return [
            MailerInterface::class => MailerInterface::class,
            Mail::class => Mail::class,
        ];
    }
}
