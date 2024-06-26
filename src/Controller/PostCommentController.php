<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
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

class PostCommentController extends AbstractController
{
    #[Route(path: '/post-comment/{slug}/add', name: 'post_comment_add', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function postcommentAdd(
        Request $request,
        #[MapEntity(mapping: ['slug' => 'slug'])] Post $post,
        CommentsService $commentsService,
        CommentRepository $commentRepository,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): Response {
        $comments = $commentRepository->findRecentComments($post);
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment = $form->getData();
                $commentsService->createdComment($form, $comment, $post, null);

                $this->addFlash('success', $translator->trans('Your comment has been sent, thank you. It will be published after validation by a moderator.'));
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }

            return $this->redirectToRoute('post_article', ['slug' => $post->getSlug()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/post-comment-form-error.html.twig', compact('comments', 'post', 'form'));
    }

    public function form(Post $post): Response
    {
        $form = $this->createForm(CommentFormType::class);

        return $this->render('post/post-comment-form.html.twig', compact('post', 'form'));
    }

    #[Route(path: '/post-comment/comment/{id<[0-9]+>}', name: 'post_comment_delete', methods: ['POST'])]
    #[Security("is_granted('ROLE_USER') and user === comment.getAuthor()")]
    public function postcommentDeleted(
        Request $request,
        CommentsService $commentsService,
        Comment $comment,
        TranslatorInterface $translator
    ): Response {
        $params = ['slug' => $comment->getPost()->getSlug()];

        if ($this->isCsrfTokenValid('comment_deletion_'.$comment->getId(), $request->request->get('csrf_token'))) {
            $commentsService->deletedComment($comment);
            $this->addFlash('success', $translator->trans('Your comment has been successfully deleted.'));
        }

        return $this->redirectToRoute('post_article', $params);
    }
}
