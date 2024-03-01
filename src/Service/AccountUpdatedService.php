<?php

namespace App\Service;

use App\DTO\AccountUpdatedDTO;
use App\DTO\AccountUpdatedAvatarDTO;
use App\DTO\AccountUpdatedSocialDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AccountUpdatedService
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly TranslatorInterface $translator,
        private readonly GeneratorTokenService $generatorTokenService,
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
    }

    public function updatedAvatar(AccountUpdatedAvatarDTO $data): void
    {
        if (false === $data->file->getRealPath()) {
            throw new \RuntimeException($this->translator->trans('Unable to resize a non-existent avatar'));
        }

        // We resize the image
        //$image = new ImageManager(['driver' => 'imagick']);
        //$image->make($data->file)->fit(110, 110)->save($data->file->getRealPath());

        // We move it to the user profile
        //$data->user->setAvatarFile($data->file);
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

    public function updatedEmail(): void
    {
        // Code
    }
}
