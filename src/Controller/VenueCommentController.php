<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Venue;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Service\CommentsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class VenueCommentController extends AbstractController
{
    #[Route(path: '/venue-comment/{slug}/add', name: 'venue_comment_add', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function venuecommentAdd(
        Request $request,
        #[MapEntity(mapping: ['slug' => 'slug'])] Venue $venue,
        CommentsService $commentsService,
        CommentRepository $commentRepository,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): Response {
        $comments = $commentRepository->findRecentComments($venue);
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment = $form->getData();
                $commentsService->createdComment($form, $comment, null, $venue);

                $this->addFlash('success', $translator->trans('Your comment has been sent, thank you. It will be published after validation by a moderator.'));
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }

            return $this->redirectToRoute('venue', ['slug' => $venue->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('venue/venue-comment-form-error.html.twig', compact('comments', 'venue', 'form'));
    }

    public function form(Venue $venue): Response
    {
        $form = $this->createForm(CommentFormType::class);

        return $this->render('venue/venue-comment-form.html.twig', compact('venue', 'form'));
    }

    #[Route(path: '/venue-comment/comment/{id<[0-9]+>}', name: 'venue_comment_delete', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER') and user === comment.getAuthor()")]
    public function venuecommentDeleted(
        Request $request,
        CommentsService $commentsService,
        Comment $comment,
        TranslatorInterface $translator
    ): Response {
        $params = ['slug' => $comment->getVenue()->getSlug()];

        if ($this->isCsrfTokenValid('comment_deletion_'.$comment->getId(), $request->request->get('csrf_token'))) {
            $commentsService->deletedComment($comment);
            $this->addFlash('success', $translator->trans('Your comment has been successfully deleted.'));
        }

        return $this->redirectToRoute('venue', $params);
    }
}
