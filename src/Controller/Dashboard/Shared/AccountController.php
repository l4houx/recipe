<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use App\DTO\AccountUpdatedSocialDTO;
use App\Service\AccountUpdatedService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AccountUpdatedProfileFormType;
use App\Form\AccountUpdatedPasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Update\AccountUpdatedSocialFormType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** MyProfile */
class AccountController extends Controller
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly AccountUpdatedService $accountUpdatedService,
        private readonly LogoutUrlGenerator $logoutUrlGenerator
    ) {
    }

    #[Route(path: '/%website_dashboard_path%/account/profile', name: 'dashboard_account_profile', methods: ['GET'])]
    #[IsGranted(HasRoles::DEFAULT)]
    public function profile(): Response
    {
        $user = $this->getUserOrThrow();

        return $this->render('dashboard/shared/account/profile.html.twig', compact('user'));
    }

    #[Route(path: '/%website_dashboard_path%/account/edit-profile', name: 'dashboard_account_edit_profile', methods: ['GET', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editProfile(Request $request): Response
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(AccountUpdatedProfileFormType::class, $user, [
            'method' => 'PATCH',
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Compte mis à jour avec succès!');

            return $this->redirectToRoute('dashboard_account_profile');
        }

        return $this->render('dashboard/shared/account/edit-profile.html.twig', compact('user', 'form'));
    }

    #[Route(path: '/%website_dashboard_path%/account/edit-change-password', name: 'dashboard_account_edit_change_password', methods: ['GET', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editchangePassword(Request $request, UserPasswordHasherInterface $hasher, LogoutUrlGenerator $logoutUrlGenerator): Response
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(AccountUpdatedPasswordFormType::class, null, [
            'current_password_is_required' => true,
            'method' => 'PATCH',
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $hasher->hashPassword($user, $form['newPassword']->getData())
            );

            $this->em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour avec succès!');

            return $this->redirectToRoute($logoutUrlGenerator->getLogoutPath());
        }

        return $this->render('dashboard/shared/account/edit-change-password.html.twig', compact('user', 'form'));
    }

    // # NEWS TEST
    #[Route(path: '/%website_dashboard_path%/account/edit', name: 'dashboard_account_edit', methods: ['GET', 'POST'])]
    #[IsGranted(HasRoles::DEFAULT)]
    public function accountEdit(Request $request): Response
    {
        $user = $this->getUserOrThrow();

        // Profile processing
        [$formEditProfile, $response] = $this->accountEditProfile($request);
        if ($response) {
            return $response;
        }

        // Password processing
        [$formEditPassword, $response] = $this->accountEditPassword($request);
        if ($response) {
            return $response;
        }

        // Social media processing
        [$formEditSocial, $response] = $this->accountEditSocialMedia($request);
        if ($response) {
            return $response;
        }

        return $this->render('dashboard/shared/account/account-edit.html.twig', compact('user', 'formEditProfile', 'formEditPassword', 'formEditSocial'));
    }

    /**
     * Edit profile form.
     */
    private function accountEditProfile(Request $request): array
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(AccountUpdatedProfileFormType::class, $user);

        if ('profile' !== $request->get('action')) {
            return [$form, null];
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Compte mis à jour avec succès!');

            return [$form, $this->redirectToRoute('dashboard_account_edit')];
        }

        return [$form, null];
    }

    /**
     * Edit password form.
     */
    private function accountEditPassword(Request $request): array
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(AccountUpdatedPasswordFormType::class, null, [
            'current_password_is_required' => true,
            'method' => 'PATCH',
        ])->handleRequest($request);

        if ('password' !== $request->get('action')) {
            return [$form, null];
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user->setPassword($this->hasher->hashPassword($user, $data['newPassword']));
            $this->em->flush();
            $this->addFlash('success', 'Mot de passe mis à jour avec succès!');

            return [$form, $this->redirect($this->logoutUrlGenerator->getLogoutPath())];
        }

        return [$form, null];
    }

    /**
     * Edit social media form.
     */
    private function accountEditSocialMedia(Request $request): array
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(AccountUpdatedSocialFormType::class, new AccountUpdatedSocialDTO($user));
        if ('social' !== $request->get('action')) {
            return [$form, null];
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->accountUpdatedService->updatedSocial($data);
            $this->em->flush();

            $this->addFlash('success', 'Vos réseaux sociaux ont été mis à jour avec succès!');

            return [$form, $this->redirectToRoute('dashboard_account_edit')];
        }

        return [$form, null];
    }
}
