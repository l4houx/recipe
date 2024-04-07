<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

/** My Profile Creator */
#[Route(path: '/%website_dashboard_path%/creator', name: 'dashboard_creator_')]
#[IsGranted(HasRoles::CREATOR)]
class ChangePasswordController extends BaseController
{
    #[Route(path: '/account-change-password', name: 'account_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $hasher,
        LogoutUrlGenerator $logoutUrlGenerator,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(ChangePasswordFormType::class, null, [
            'current_password_is_required' => true,
            'method' => 'PATCH',
        ])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user->setPassword($hasher->hashPassword($user, $form['newPassword']->getData()));
                $em->flush();
                $this->addFlash('info', $translator->trans('Content was edited successfully.'));

                return $this->redirect($logoutUrlGenerator->getLogoutPath());
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/account/creator/change_password.html.twig', compact('form'));
    }
}
