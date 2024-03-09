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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class CommentService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $stack,
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

        $this->flash->add('success', 'Votre commentaire a été envoyé, merci. Il sera publié après validation par un modérateur!');
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

        $this->flash->add('danger', 'Votre commentaire a été supprimé avec succès!');
    }
}
