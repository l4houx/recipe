<?php

namespace App\Controller\KnpUOAuth;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\DiscordClient;
use KnpU\OAuth2ClientBundle\Exception\MissingAuthorizationCodeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

#[IsGranted(HasRoles::DEFAULT)]
class DiscordController extends BaseController
{
    #[Route(path: '/discord/connect', name: 'oauth_discord', methods: ['GET'])]
    public function connect(DiscordClient $client): RedirectResponse
    {
        return $client->redirect(['identify', 'email']);
    }

    #[Route(path: '/oauth/check/discord', name: 'oauth_discord_check', methods: ['GET'])]
    public function check(
        DiscordClient $client,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): RedirectResponse {
        try {
            /** @var DiscordResourceOwner $discordUser */
            $discordUser = $client->fetchUser();

            /** @var User $user */
            $user = $this->getUser();

            $user->setDiscordId($discordUser->getId());
            $em->flush();

            $this->addFlash('success', $translator->trans('Your discord account has been successfully linked'));
        } catch (MissingAuthorizationCodeException) {
            // Do nothing
        }

        return $this->redirectToRoute('dashboard_main_account', [], Response::HTTP_SEE_OTHER);
    }
}
