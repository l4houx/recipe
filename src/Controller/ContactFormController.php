<?php

namespace App\Controller;

use App\DTO\ContactFormDTO;
use App\Form\ContactFormType;
use App\Service\SendMailService;
use App\Event\ContactRequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactFormController extends AbstractController
{
    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contactForm(Request $request, /*SendMailService $mail,*/ EventDispatcherInterface $eventDispatcher): Response
    {
        $data = new ContactFormDTO();

        // TODO : Supprimer ca
        $data->name = 'John Doe';
        $data->email = 'john-doe@example.com';
        $data->message = 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Commodi quae fugiat quidem velit quisquam nemo quis blanditiis at id impedit magnam, fugit aperiam harum, est doloribus hic maiores molestiae earum.';
        // FIN TODO

        $form = $this->createForm(ContactFormType::class, $data)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*
            $mail->send(
                $data->service,
                $data->email,
                'Demande de contact',
                'contact',
                compact('data')
            );
            */

            $eventDispatcher->dispatch(new ContactRequestEvent($data));
            $this->addFlash('success', 'Votre message a été envoyé avec succès, merci.');

            /*
            try {
                $eventDispatcher->dispatch(new ContactRequestEvent($data));
                $this->addFlash('success', 'Votre message a été envoyé avec succès, merci.');
            } catch (\Exception $e) {
                $this->addFlash('danger', "Impossible d'envoyer votre email.");
            }
            */

            //$this->addFlash('success', 'Votre message a été envoyé avec succès, merci.');

            return $this->redirectToRoute('contact', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/contact-form.html.twig', compact('data', 'form'));
    }
}
