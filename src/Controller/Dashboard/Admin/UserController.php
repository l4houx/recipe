<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\User;
use App\Form\UserFormType;
use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-users', name: 'dashboard_admin_user_')]
#[IsGranted(HasRoles::ADMIN)]
class UserController extends Controller
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('dashboard/admin/user/index.html.twig', compact('users'));
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
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

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('User was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/user/new.html.twig', compact('user', 'form'));
    }
}
