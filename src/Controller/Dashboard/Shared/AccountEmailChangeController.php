<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use App\Entity\UserEmailVerification;
use App\Repository\UserRepository;
use App\Service\AccountUpdatedService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/** MyProfile */
#[Route(path: '/%website_dashboard_path%/account')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountEmailChangeController extends Controller
{
    #[Route(path: '/email-confirm/{token}', name: 'dashboard_account_user_email_confirm')]
    public function confirm(
        UserEmailVerification $userEmailVerification,
        TranslatorInterface $translator,
        AccountUpdatedService $accountUpdatedService,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        if ($userEmailVerification->isExpired()) {
            $this->addFlash('danger', $translator->trans('This confirmation request has timed out'));
        } else {
            $user = $userRepository->findOneByEmail($userEmailVerification->getEmail());

            if ($user) {
                $this->addFlash('danger', $translator->trans('This email has already been used'));

                return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
            }

            $accountUpdatedService->updatedEmail($userEmailVerification);

            $em->flush();

            $this->addFlash('success', $translator->trans('Your email has been changed successfully'));
        }

        return $this->redirectToRoute('dashboard_account_edit', [], Response::HTTP_SEE_OTHER);
    }
}
