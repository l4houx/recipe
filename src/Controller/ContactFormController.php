<?php

namespace App\Controller;

use App\DTO\ContactFormDTO;
use App\Form\ContactFormType;
use App\Service\SendMailService;
use App\Event\ContactRequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactFormController extends AbstractController
{
    #[Route(path: '/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contactForm(Request $request, /*SendMailService $mail,*/ TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher): Response
    {
        $data = new ContactFormDTO();

        // TODO : Supprimer ca
        $data->name = 'John Doe';
        $data->email = 'john-doe@example.com';
        $data->message = 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Commodi quae fugiat quidem velit quisquam nemo quis blanditiis at id impedit magnam, fugit aperiam harum, est doloribus hic maiores molestiae earum.';
        // FIN TODO

        $form = $this->createForm(ContactFormType::class, $data)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventDispatcher->dispatch(new ContactRequestEvent($data));
            $this->addFlash('success', $translator->trans('Your email has been sent, you will receive a response as soon as possible.'));

            return $this->redirectToRoute('contact', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/contact-form.html.twig', compact('data', 'form'));
    }
}
