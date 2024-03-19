<?php

namespace App\EventSubscriber\Security;

use App\Event\Account\AccountDeletedEvent;
use App\Event\Account\AccountUserCreatedEvent;
use App\Infrastructural\Mail\Mail;
use App\Service\AccountDeletedService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $params,
        private readonly Mail $mail
    ) {
    }

    /*
    public function onPasswordRequest(PasswordResetTokenCreatedEvent $event): void
    {
        $email = $this->mail->sendEmail('mail/security/password_reset.html.twig', [
            'token' => $event->getToken()->getToken(),
            'id' => $event->getUser()->getId(),
            'username' => $event->getUser()->getUsername(),
        ])
            ->to($event->getUser()->getEmail())
            ->subject($this->params->get('website_name') . '' . $this->translator->trans(' | Resetting your password'));
        $this->mail->send($email);
    }
    */

    public function onRegister(AccountUserCreatedEvent $event): void
    {
        if ($event->isUsingOauth()) {
            return;
        }

        $email = $this->mail->sendEmail('mail/security/register.html.twig', [
            'user' => $event->getUser(),
        ])
            ->to($event->getUser()->getEmail())
            ->subject($this->params->get('website_name').''.$this->translator->trans(' | Confirming the account'))
        ;

        $this->mail->send($email);
    }

    public function onDelete(AccountDeletedEvent $event): void
    {
        $email = $this->mail->sendEmail('mail/security/delete.html.twig', [
            'user' => $event->getUser(),
            'days' => AccountDeletedService::DAYS,
        ])
            ->to($event->getUser()->getEmail())
            ->subject($this->params->get('website_name').''.$this->translator->trans(' | Delete your account'))
        ;

        $this->mail->send($email);
    }

    /**
     * @return array<string,string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // PasswordResetTokenCreatedEvent::class => 'onPasswordRequest',
            AccountUserCreatedEvent::class => 'onRegister',
            AccountDeletedEvent::class => 'onDelete',
        ];
    }
}
