<?php

namespace App\Service;

use App\DTO\ContactFormDTO;
use App\Entity\ContactRequest;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContactRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use App\Security\Exception\ContactRequestException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContactRequestService
{
    public function __construct(
        private readonly ContactRequestRepository $contactRequestRepository,
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $params,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer
    ) {
    }

    public function send(Request $request, ContactFormDTO $data): void
    {
        $contactRequest = (new ContactRequest())->setRawIp($request->getClientIp());

        $lastRequest = $this->contactRequestRepository->findLastIPRequest($contactRequest->getIp());

        if ($lastRequest && $lastRequest->getCreatedAt() > new \DateTimeImmutable('- 1 hour')) {
            throw new ContactRequestException();
        }

        if (null !== $lastRequest) {
            $lastRequest->setCreatedAt(new \DateTimeImmutable());
        } else {
            $this->em->persist($contactRequest);
        }

        $this->em->flush();

        $message = (new Email())
            ->from(new Address(
                $this->params->get('website_no_reply_email'),
                $this->params->get('website_name'),
            ))
            ->to(new Address(
                $this->params->get('website_contact_email'),
            ))
            ->replyTo(new Address($data->email, $data->name))
            ->subject($this->translator->trans('Contact Request') . ": {$data->name}")
            ->text($data->message)
        ;

        $this->mailer->send($message);
    }
}
