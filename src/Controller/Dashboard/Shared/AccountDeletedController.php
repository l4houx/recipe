<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Service\AccountDeletedService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/** MyProfile */
#[Route(path: '/%website_dashboard_path%/account')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountDeletedController extends BaseController
{
    #[Route(path: '/', methods: ['DELETE'])]
    public function accountDeleted(
        Request $request,
        AccountDeletedService $accountDeletedService,
        UserPasswordHasherInterface $hasher,
        TranslatorInterface $translator
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!$this->isCsrfTokenValid('account_deleted_', $data['csrf'] ?? '')) {
            return new JsonResponse([
                'title' => $translator->trans('Invalid CSRF token.'),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$hasher->isPasswordValid($user, $data['password'] ?? '')) {
            return new JsonResponse([
                'title' => $translator->trans('Unable to delete account, invalid password.'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        $accountDeletedService->accountDeleted($user, $request);

        return new JsonResponse([
            'message' => 'Your account deletion request has been taken into account. Your account will be automatically deleted after '.AccountDeletedService::DAYS.' days',
        ]);
    }

    #[Route(path: '/deleted-cancel', name: 'dashboard_account_deleted_cancel', methods: ['POST'])]
    public function accountDeletedCancel(EntityManagerInterface $em, TranslatorInterface $translator): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setDeletedAt(null);
        $em->flush();

        $this->addFlash('success', $translator->trans('The deletion of your account has been successfully canceled.'));

        return $this->redirectToRoute('dashboard_account_edit', [], Response::HTTP_SEE_OTHER);
    }
}
