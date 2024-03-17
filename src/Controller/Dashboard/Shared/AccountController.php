<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use App\DTO\AccountUpdatedSocialDTO;
use App\Service\AccountUpdatedService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AccountUpdatedSocialFormType;
use App\Form\AccountUpdatedProfileFormType;
use App\Form\AccountUpdatedPasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** MyProfile */
#[Route(path: '/%website_dashboard_path%/account', name: 'dashboard_account_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountController extends Controller
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly AccountUpdatedService $accountUpdatedService,
        private readonly LogoutUrlGenerator $logoutUrlGenerator,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/profile', name: 'profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUserOrThrow();

        return $this->render('dashboard/shared/account/profile.html.twig', compact('user'));
    }

    #[Route(path: '/edit', name: 'edit', methods: ['GET', 'POST'])]
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

        return $this->render('dashboard/shared/account/edit.html.twig', compact('user', 'formEditProfile', 'formEditPassword', 'formEditSocial'));
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

            $this->addFlash('success', $this->translator->trans('Account updated successfully!'));

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
            $this->addFlash('success', $this->translator->trans('Password updated successfully!'));

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

            $this->addFlash('success', $this->translator->trans('Your social networks have been updated successfully!'));

            return [$form, $this->redirectToRoute('dashboard_account_edit')];
        }

        return [$form, null];
    }
}
