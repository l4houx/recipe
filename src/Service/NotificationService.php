<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Review;
use App\Entity\User;
use App\Event\Notification\NotificationCreatedEvent;
use App\Event\Notification\NotificationReadEvent;
use App\Infrastructural\Normalizer\Path\Encoder;
use App\Repository\NotificationRepository;
use App\Security\Voter\ChannelVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationService
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Security $security
    ) {
    }

    /**
     * Sends a notification on a particular channel.
     */
    public function notifyChannel(string $channel, string $message, ?object $entity = null): Notification
    {
        /** @var string $url */
        $url = $entity ? $this->serializer->serialize($entity, Encoder::FORMAT) : null;

        $notification = (new Notification())
            ->setMessage($message)
            ->setUrl($url)
            ->setTarget($entity ? $this->getHashForEntity($entity) : null)
            ->setCreatedAt(new \DateTime())
            ->setChannel($channel)
        ;

        $this->em->persist($notification);
        $this->em->flush();

        $this->dispatcher->dispatch(new NotificationCreatedEvent($notification));

        return $notification;
    }

    /**
     * Send a notification to a user.
     */
    public function notifyUser(User $user, string $message, object $entity): Notification
    {
        /** @var string $url */
        $url = $this->serializer->serialize($entity, Encoder::FORMAT);

        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);

        // If we notify about a review, the target becomes the recipe
        if ($entity instanceof Review) {
            $entity = $entity->getRecipe();
        }

        $notification = (new Notification())
            ->setMessage($message)
            ->setUrl($url)
            ->setTarget($this->getHashForEntity($entity))
            ->setCreatedAt(new \DateTime())
            ->setUser($user)
        ;

        $notificationRepository->persistOrUpdate($notification);

        $this->em->flush();
        $this->dispatcher->dispatch(new NotificationCreatedEvent($notification));

        return $notification;
    }

    /**
     * @return Notification[]
     */
    public function forUser(User $user): array
    {
        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->em->getRepository(Notification::class);

        return $notificationRepository->findRecentForUser($user, $this->getChannelsForUser($user));
    }

    public function readAll(User $user): void
    {
        $user->setNotificationsReadAt(new \DateTime());

        $this->em->flush();

        $this->dispatcher->dispatch(new NotificationReadEvent($user));
    }

    /**
     * Returns the channels the user can subscribe to.
     *
     * @return string[]
     */
    public function getChannelsForUser(User $user): array
    {
        $channels = [
            'user/'.$user->getId(),
            'public',
        ];

        if ($this->security->isGranted(ChannelVoter::LISTEN_ADMIN)) {
            $channels[] = 'admin';
        }

        return $channels;
    }

    /**
     * Extract a hash for a notification className::id.
     */
    private function getHashForEntity(object $entity): string
    {
        $hash = $entity::class;

        if (method_exists($entity, 'getId')) {
            $hash .= '::'.(string) $entity->getId();
        }

        return $hash;
    }
}
