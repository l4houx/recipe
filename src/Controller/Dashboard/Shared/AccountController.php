<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use App\Form\ChangePasswordFormType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

/** MyProfile */
class AccountController extends Controller
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
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

        $form = $this->createForm(UserFormType::class, $user, [
            'method' => 'PATCH',
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Compte mis à jour avec succès!');

            return $this->redirectToRoute('dashboard_account_profile');
        }

        return $this->render('dashboard/shared/account/edit-profile.html.twig', compact('user', 'form'));
    }

    #[Route(path: '/%website_dashboard_path%/account/edit-avatar', name: 'dashboard_account_edit_avatar', methods: ['GET', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editAvatar(): Response
    {
        $user = $this->getUserOrThrow();

        // code...

        return $this->render('dashboard/shared/account/edit-avatar.html.twig', compact('user'));
    }

    #[Route(path: '/%website_dashboard_path%/account/edit-change-password', name: 'dashboard_account_edit_change_password', methods: ['GET', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editchangePassword(Request $request, UserPasswordHasherInterface $hasher, LogoutUrlGenerator $logoutUrlGenerator): Response
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(ChangePasswordFormType::class, null, [
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
    #[Route(path: '/%website_dashboard_path%/account/edit', name: 'dashboard_account_edit', methods: ['GET', 'PATCH'])]
    public function accountEdit(Request $request): Response
    {
        $users = $this->getUserOrThrow();

        // Profile processing
        [$formEditProfile, $response] = $this->accountEditProfile($request);
        if ($response) {
            return $response;
        }

        // Avatar processing
        [$formEditAvatar, $response] = $this->accountEditAvatar($request);
        if ($response) {
            return $response;
        }

        // Password processing
        [$formEditPassword, $response] = $this->accountEditPassword($request);
        if ($response) {
            return $response;
        }

        return $this->render('dashboard/shared/account/account-edit.html.twig', compact('formEditProfile', 'formEditAvatar', 'formEditPassword'));
    }

    /**
     * Edit profile form.
     */
    private function accountEditProfile(Request $request): array
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(UserFormType::class, $user, [
            'method' => 'PATCH',
        ])->handleRequest($request);

        if ('profile' !== $request->get('action')) {
            return [$form, null];
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Compte mis à jour avec succès!');

            return [$form, $this->redirectToRoute('dashboard_account_profile')];
        }

        return [$form, null];
    }

    /**
     * Edit avatar form.
     */
    private function accountEditAvatar(Request $request): array
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(UserFormType::class, $user, [
            'method' => 'PATCH',
        ])->handleRequest($request);

        if ('profile' !== $request->get('action')) {
            return [$form, null];
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Compte mis à jour avec succès!');

            return [$form, $this->redirectToRoute('dashboard_account_profile')];
        }

        return [$form, null];
    }

    /**
     * Edit password form.
     */
    private function accountEditPassword(Request $request): array
    {
        $user = $this->getUserOrThrow();

        $form = $this->createForm(ChangePasswordFormType::class, null, [
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
}
