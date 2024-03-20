<?php

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/** MyProfile */
#[Route(path: '/%website_dashboard_path%/account', name: 'dashboard_account_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountController extends BaseController
{
    #[Route(path: '/profile/theme', name: 'profile_theme', methods: ['POST'])]
    public function theme(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $theme = $data['theme'] ?? null;
        if (!in_array($theme, ['light', 'dark'])) {
            return $this->json(['title' => $translator->trans('This theme is not supported.')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->getUserOrThrow()->setTheme($theme);

        $em->flush();

        return $this->json(null);
    }
}
