<?php

namespace App\EventSubscriber\Email;

use App\Entity\User;
use App\Service\SendMailService;
use App\Event\ContactRequestEvent;
use App\Event\HelpCenterSupportRequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SendMailService $mail,
        private readonly ParameterBagInterface $params
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ContactRequestEvent::class => 'onContactRequestEvent',
            HelpCenterSupportRequestEvent::class => 'onHelpCenterSupportRequestEvent',
            InteractiveLoginEvent::class => 'onLogin',
        ];
    }

    public function onContactRequestEvent(ContactRequestEvent $event): void
    {
        $data = $event->data;

        $this->mail->send(
            $data->service,
            $data->email,
            $this->translator->trans('Request contact'),
            'contact',
            compact('data')
        );
    }

    public function onHelpCenterSupportRequestEvent(HelpCenterSupportRequestEvent $event): void
    {
        $data = $event->data;

        $this->mail->send(
            $data->service,
            $data->email,
            $this->translator->trans('Request support'),
            'support',
            compact('data')
        );
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        $this->mail->send(
            $user->getEmail(),
            $this->params->get('website_no_reply_email'),
            'Connexion',
            'login',
            compact('user')
        );
    }
}
