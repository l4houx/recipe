<?php

namespace App\Controller\Dashboard\Shared\Application;

use App\Entity\User;
use App\Entity\Application;
use App\Entity\Traits\HasRoles;
use App\Form\ApplicationFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/%website_dashboard_path%/application', name: 'dashboard_application_')]
#[IsGranted(HasRoles::DEFAULT)]
class ApplicationController extends AbstractController
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(
        #[CurrentUser] ?User $user,
        ApplicationRepository $applicationRepository
    ): Response {
        if (null === $user) {
            return $this->redirectToRoute('login');
        }

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $applications = $applicationRepository->findAll();
        } else {
            $applications = $applicationRepository->findBy(['user' => $user->getId()]);
        }

        return $this->render('dashboard/shared/application/index.html.twig', compact('applications'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        #[CurrentUser] ?User $user,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $application = new Application();
        $form = $this->createForm(ApplicationFormType::class, $application)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application->setUser($user);

            $em->persist($application);
            $em->flush();

            $this->addFlash('success', $translator->trans('Content was created successfully.'));

            return $this->redirectToRoute('dashboard_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/shared/application/new.html.twig', compact('application', 'form'));
    }

    #[Route(path: '/token/{id}', name: 'token', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function token(
        Application $application,
        #[CurrentUser] ?User $user,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        if ($application->getUser() !== $user && !$this->security->isGranted(HasRoles::ADMIN)) {
            $this->addFlash('secondary', $translator->trans('Application in you does not belong.'));

            return $this->redirectToRoute('dashboard_application_index', [], Response::HTTP_SEE_OTHER);
        }

        $length = 64;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $tokenPlain = '';
        for ($i = 0; $i < $length; ++$i) {
            $tokenPlain .= $characters[random_int(0, $charactersLength - 1)];
        }
        // crypter le token
        $salt = $this->getParameter('website_security_alt');
        $token = crypt($tokenPlain, $salt);

        $application->setToken($token);
        $em->persist($application);
        $em->flush();

        $this->addFlash('success', $translator->trans('Please note the following token : '.$tokenPlain."\n It will no longer be searchable."));

        return $this->redirectToRoute('dashboard_application_index', [], Response::HTTP_SEE_OTHER);
    }
}
