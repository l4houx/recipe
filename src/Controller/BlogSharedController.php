<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostSharedFormType;
use App\Service\SendMailService;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/blog')]
class BlogSharedController extends AbstractController
{
    #[Route('/{slug}/shared', name: 'blog_shared', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET', 'POST'])]
    public function blogShared(
        Request $request,
        Post $post,
        SendMailService $mail, 
        TranslatorInterface $translator
    ): Response {
        if (!$post) {
            $this->addFlash('secondary', $translator->trans('The article not be found'));
            return $this->redirectToRoute('blog_list');
        }

        $appErrors = [];

        $form = $this->createForm(PostSharedFormType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $subject = sprintf('%s advises you to read "%s"', $data['sender_name'], $post->getTitle());

            $mail->send(
                $this->getParameter('website_no_reply_email'),
                $data['receiver_email'],
                $subject,
                'blog-shared',
                [
                    'post' => $post,
                    'sender_name' => $data['sender_name'],
                    'sender_comments' => $data['sender_comments'],
                ],
            );

            $this->addFlash('success', $translator->trans('🚀 Post successfully shared with your friend!'));

            return $this->redirectToRoute('blog_list');
        } elseif ($form->isSubmitted()) {
            /** @var FormError $error */
            foreach ($form->getErrors() as $error) {
                if (null === $error->getCause()) {
                    $appErrors[] = $error;
                }
            }
        }

        return $this->render('blog/blog-shared.html.twig', [
            'errors' => $appErrors,
            'post' => $post,
            'form' => $form,
        ]);
    }
}
