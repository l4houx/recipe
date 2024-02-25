<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendMailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $params
    ) {
    }

    /** @throws TransportExceptionInterface */
    public function send(
        string $to,
        string $from,
        string $subject,
        string $template,
        array $context
    ): void {
        // We create the email
        $email = (new TemplatedEmail())
            ->to(new Address(
                $to,
                $this->params->get('website_name'),
            ))
            ->from(new Address($from))
            ->subject($subject)
            ->htmlTemplate("mails/$template.html.twig")
            ->context($context)
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $transport) {
            throw $transport;
        }
    }
}
