<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Entity\Venue;
use App\Entity\Comment;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentsService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly SecurityService $securityService
    ) {
    }

    public function createdComment(
        FormInterface $form,
        Comment $comment,
        ?Post $post = null,
        ?Venue $venue = null
    ): void {
        $comment
            ->setIp($this->requestStack->getMainRequest()?->getClientIp())
            //->setContent()
            ->setAuthor($this->securityService->getUserOrNull())
            ->setPost($post)
            ->setVenue($venue)
            ->setIsApproved(false)
            ->setIsRGPD(true)
            ->setPublishedAt(new \DateTimeImmutable('now'))
        ;

        $parentid = $form->get('parentid')->getData();
        if (null !== $parentid) {
            $parent = $this->em->getRepository(Comment::class)->find($parentid);
        }

        $comment->setParent($parent ?? null);

        $this->em->persist($comment);
        $this->em->flush();
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
    }
}
