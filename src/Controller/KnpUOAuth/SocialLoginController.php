<?php

namespace App\Controller\KnpUOAuth;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class SocialLoginController extends BaseController
{
    private const SCOPES = [
        'github' => ['user:email'],
        'google' => [],
        'facebook' => ['email'],
    ];

    public function __construct(private readonly ClientRegistry $clientRegistry)
    {
    }

    #[Route(path: '/oauth/connect/{service}', name: 'oauth_connect', methods: ['GET'])]
    public function connect(string $service): RedirectResponse
    {
        $this->ensureServiceAccepted($service);

        return $this->clientRegistry->getClient($service)->redirect(self::SCOPES[$service], ['a' => 1]);
    }

    #[Route(path: '/oauth/unlink/{service}', name: 'oauth_unlink', methods: ['GET'])]
    #[IsGranted(HasRoles::DEFAULT)]
    public function disconnect(
        string $service,
        SecurityService $securityService,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): RedirectResponse {
        $this->ensureServiceAccepted($service);
        $method = 'set'.ucfirst($service).'Id';
        $securityService->getUser()->$method(null);
        $em->flush();

        $this->addFlash('success', $translator->trans('Your account has been successfully unlinked from '.$service));

        return $this->redirectToRoute('dashboard_creator_account_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/oauth/check/{service}', name: 'oauth_check', methods: ['GET'])]
    public function check(): Response
    {
        return new Response();
    }

    private function ensureServiceAccepted(string $service): void
    {
        if (!\in_array($service, array_keys(self::SCOPES), true)) {
            throw new AccessDeniedException();
        }
    }
}
