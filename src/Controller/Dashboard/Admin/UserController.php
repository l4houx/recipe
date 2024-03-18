<?php

namespace App\Controller\Dashboard\Admin;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use App\Security\Exception\PremiumNotBanException;
use App\Service\AccountSuspendedService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-users', name: 'dashboard_admin_user_')]
#[IsGranted(HasRoles::ADMIN)]
class UserController extends Controller
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('dashboard/admin/user/index.html.twig', compact('users'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $form->has('plainPassword') ? $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ) : ''
            );
            $user->setLastLoginIp($request->getClientIp());

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('User was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/user/new.html.twig', compact('user', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('user_deletion_'.$user->getId(), $request->request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('User was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{id}/suspended', name: 'suspended', methods: ['POST', 'DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function suspended(Request $request, User $user, AccountSuspendedService $accountSuspendedService): Response
    {
        $username = $user->getUsername();

        try {
            $accountSuspendedService->suspended($user);
            $this->em->flush();
        } catch (PremiumNotBanException) {
            $this->addFlash('danger', $this->translator->trans('Unable to suspended a premium user.'));

            return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([]);
        }

        $this->addFlash('success', $this->translator->trans("User $username has been suspended"));

        return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{id}/verified', name: 'verified', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function verified(User $user): RedirectResponse
    {
        $user->setIsVerified(true);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('The account has been verified successfully.'));

        return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
