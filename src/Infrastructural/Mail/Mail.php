<?php

namespace App\Infrastructural\Mail;

use Twig\Environment;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use App\Infrastructural\Messenger\EnqueueMethod;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Mail
{
    public function __construct(
        private readonly Environment $twig,
        private readonly EnqueueMethod $enqueue,
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $params,
        private readonly ?string $dkimKey = null
    ) {
    }

    public function sendEmail(string $template, array $data = []): Email
    {
        $this->twig->addGlobal('format', 'html');
        $html = $this->twig->render($template, array_merge($data, ['layout' => 'mail/base.html.twig']));
        $this->twig->addGlobal('format', 'text');
        $text = $this->twig->render($template, array_merge($data, ['layout' => 'mail/base.text.twig']));

        $from = new Address($this->params->get('website_no_reply_email'), $this->params->get('website_name'));

        return (new Email())
            ->from($from)
            ->html($html)
            ->text($text)
        ;
    }

    public function send(Email $email): void
    {
        $this->enqueue->enqueue(self::class, 'sendNow', [$email]);
    }

    public function sendNow(Email $email): void
    {
        if ($this->dkimKey) {
            $dkimSigner = new DkimSigner("file://{$this->dkimKey}", $this->params->get('website_name'), 'default');

            // We sign a message while waiting for the fix https://github.com/symfony/symfony/issues/40131
            $message = new Message($email->getPreparedHeaders(), $email->getBody());
            $email = $dkimSigner->sign($message, []);
        }

        $this->mailer->send($email);
    }
}
