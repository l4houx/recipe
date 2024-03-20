<?php

namespace App\EventSubscriber\Security;

use App\Event\Email\UserEmailVerificationEvent;
use App\Infrastructural\Mail\Mail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $params,
        private readonly Mail $mail
    ) {
    }

    public function onEmailChange(UserEmailVerificationEvent $event): void
    {
        $email = $this->mail->sendEmail('mail/account/email-confirmation.html.twig', [
            'token' => $event->userEmailVerification->getToken(),
            'username' => $event->userEmailVerification->getAuthor()->getUsername(),
        ])
            ->to($event->userEmailVerification->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($this->params->get('website_name').''.$this->translator->trans(' | Updating your email address'))
        ;

        $this->mail->send($email);

        $email = $this->mail->sendEmail('mail/account/email-notification.html.twig', [
            'username' => $event->userEmailVerification->getAuthor()->getUsername(),
            'email' => $event->userEmailVerification->getEmail(),
        ])
            ->to($event->userEmailVerification->getAuthor()->getEmail())
            ->subject($this->params->get('website_name').''.$this->translator->trans(' | Request for email change pending'))
        ;

        $this->mail->send($email);
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserEmailVerificationEvent::class => 'onEmailChange',
        ];
    }
}
