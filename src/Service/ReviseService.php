<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Revise;
use App\Entity\User;
use App\Event\Post\ReviseSubmittedEvent;
use App\Repository\ReviseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ReviseService
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ReviseRepository $reviseRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * Suggests a change to the parent.
     */
    public function submitRevise(Revise $revise): void
    {
        $revise->setCreatedAt(new \DateTime());
        $revise->setComment(null);
        $revise->setStatus(Revise::PENDING);

        $isNew = null === $revise->getId();
        if ($isNew) {
            $this->em->persist($revise);
        }

        $this->em->flush();

        if ($isNew) {
            $this->eventDispatcher->dispatch(new ReviseSubmittedEvent($revise));
        }
    }

    /**
     * Returns the current revise for the post/user or generates a new revise.
     */
    public function reviseFor(User $user, Post $post): Revise
    {
        $revise = $this->reviseRepository->findFor($user, $post);
        if (null !== $revise) {
            return $revise;
        }

        return (new Revise())
            ->setContent($post->getContent() ?: '')
            ->setTarget($post)
            ->setAuthor($user)
        ;
    }
}
