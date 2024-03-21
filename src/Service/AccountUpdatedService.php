<?php

namespace App\Service;

use App\DTO\AccountUpdatedDTO;
use App\DTO\AccountUpdatedAvatarDTO;
use App\DTO\AccountUpdatedSocialDTO;
use Intervention\Image\ImageManager;
use App\Entity\UserEmailVerification;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Imagick\Driver;
use App\Event\Email\UserEmailVerificationEvent;
use App\Repository\UserEmailVerificationRepository;
use App\Security\Exception\UserEmailChangeException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AccountUpdatedService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly GeneratorTokenService $generatorTokenService,
        private readonly TranslatorInterface $translator,
        private readonly UserEmailVerificationRepository $userEmailVerificationRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function updatedProfile(AccountUpdatedDTO $data): void
    {
        // Contact
        $data->user->setEmail($data->email);

        // Profile
        $data->user->setFirstname($data->firstname);
        $data->user->setLastname($data->lastname);
        $data->user->setUsername($data->username);

        // Pays
        $data->user->setCountry($data->country);

        // User Theme
        if (true === $data->useSystemTheme) {
            $data->user->setTheme(null);
        } else {
            $data->user->setTheme($data->useDarkTheme ? 'dark' : 'light');
        }

        // User Locale

        // User Email
        if ($data->email !== $data->user->getEmail()) {
            $lastRequest = $this->userEmailVerificationRepository->findLastForUser($data->user);

            if ($lastRequest && $lastRequest->getCreatedAt() > new \DateTime('-1 hour')) {
                throw new UserEmailChangeException($lastRequest);
            } else {
                if ($lastRequest) {
                    $this->em->remove($lastRequest);
                }
            }

            $userEmailVerification = (new UserEmailVerification())
                ->setEmail($data->email)
                ->setAuthor($data->user)
                ->setCreatedAt(new \DateTime())
                ->setToken($this->generatorTokenService->generateToken())
            ;

            $this->em->persist($userEmailVerification);
            $this->dispatcher->dispatch(new UserEmailVerificationEvent($userEmailVerification));
        }
    }

    public function updatedAvatar(AccountUpdatedAvatarDTO $data): void
    {
        if (false === $data->file->getRealPath()) {
            throw new \RuntimeException($this->translator->trans('Unable to resize a non-existent avatar'));
        }

        // We resize the image
        $image = new ImageManager(new Driver());
        $image->read($data->file)->resize(110, 110)->save($data->file->getRealPath());

        // We move it to the user profile
        // $data->user->setAvatarFile($data->file);
        $data->user->setUpdatedAt(new \DateTimeImmutable());
    }

    public function updatedSocial(AccountUpdatedSocialDTO $data): void
    {
        // External link
        $data->user->getExternallink($data->externallink);
        // Social Media
        $data->user->getYoutubeurl($data->youtubeurl);
        $data->user->getTwitterUrl($data->twitterurl);
        $data->user->getInstagramUrl($data->instagramurl);
        $data->user->getFacebookUrl($data->facebookurl);
        $data->user->getGoogleplusUrl($data->googleplusurl);
        $data->user->getLinkedinUrl($data->linkedinurl);
    }

    public function updatedEmail(UserEmailVerification $userEmailVerification): void
    {
        $userEmailVerification->getAuthor()->setEmail($userEmailVerification->getEmail());
        $this->em->remove($userEmailVerification);
    }
}
