<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Entity\Recipe;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class CommentService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $stack,
        private readonly TranslatorInterface $translator,
        private readonly FlashBagInterface $flash,
        private readonly Security $security
    ) {
    }

    public function createdComment(
        Comment $comment,
        Post $post = null,
        Recipe $recipe = null
    ): void {
        $comment
            ->setIp($this->stack->getMainRequest()?->getClientIp())
            ->setPost($post)
            ->setRecipe($recipe)
            ->setIsApproved(false)
            ->setPublishedAt(new \DateTimeImmutable('now'))
            ->setAuthor($this->security->getUser())
        ;

        $this->em->persist($comment);
        $this->em->flush();

        $this->flash->add('success', $this->translator->trans('Your comment has been sent, thank you. It will be published after validation by a moderator!'));
    }

    public function updatedComment(Comment $comment, string $content): Comment
    {
        $comment->setContent($content);
        $this->em->flush();

        return $comment;
    }

    public function deletedComment(Comment $comment): void
    {
        $this->em->remove($comment);
        $this->em->flush();

        $this->flash->add('danger', $this->translator->trans('Your comment has been deleted successfully!'));
    }
}
