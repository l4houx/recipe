<?php

namespace App\Service;

use App\DTO\CommentDTO;
use App\Entity\Comment;
use App\Entity\Post;
use App\Event\Post\CommentCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostCommentService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly TranslatorInterface $translator,
        private readonly FlashBagInterface $flash,
        private readonly SecurityService $securityService
    ) {
    }

    public function createdComment(CommentDTO $data): Comment
    {
        /** @var Post $post */
        $post = $this->em->getRepository(Post::class)->find($data->target);

        /** @var Comment|null $parent */
        $parent = $data->parent ? $this->em->getReference(Comment::class, $data->parent) : null;

        $comment = (new Comment())
            ->setIp($this->requestStack->getMainRequest()?->getClientIp())
            //->setUsername($data->username)
            ->setContent($data->content)
            ->setAuthor($this->securityService->getUserOrNull())
            ->setPost($post)
            ->setParent($parent)
            ->setIsApproved(false)
            ->setIsRGPD(true)
            ->setPublishedAt(new \DateTimeImmutable('now'))
        ;

        $this->em->persist($comment);
        $this->em->flush();

        $this->dispatcher->dispatch(new CommentCreatedEvent($comment));

        $this->flash->add('success', $this->translator->trans('Your comment has been sent, thank you. It will be published after validation by a moderator!'));

        return $comment;
    }

    public function updatedComment(Comment $comment, string $content): Comment
    {
        $comment->setContent($content);
        $this->em->flush();

        return $comment;
    }

    public function deletedComment(int $commentId): void
    {
        /** @var Comment $comment */
        $comment = $this->em->getReference(Comment::class, $commentId);

        $this->em->remove($comment);
        $this->em->flush();

        $this->flash->add('danger', $this->translator->trans('Your comment has been deleted successfully!'));
    }
}
