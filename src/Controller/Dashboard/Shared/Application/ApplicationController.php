<?php

namespace App\Controller\Dashboard\Shared\Application;

use App\Controller\BaseController;
use App\Entity\Application;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\ApplicationFormType;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class ApplicationController extends BaseController
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    #[Route(path: '/creator/my-applications', name: 'dashboard_creator_application_index', methods: ['GET'])]
    #[Route(path: '/admin/manage-applications', name: 'dashboard_admin_application_index', methods: ['GET'])]
    public function index(
        Request $request,
        #[CurrentUser] ?User $user,
        ApplicationRepository $applicationRepository,
        PaginatorInterface $paginator
    ): Response {
        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $query = $applicationRepository->findAlls();
        } else {
            $query = $applicationRepository->findBy(['user' => $user->getId()]);
        }

        $rows = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            HasLimit::APP_LIMIT,
            ['wrap-queries' => true]
        );

        return $this->render('dashboard/shared/application/index.html.twig', compact('user', 'rows'));
    }

    #[Route(path: '/creator/my-applications/new', name: 'dashboard_creator_application_new', methods: ['GET', 'POST'])]
    #[Route(path: '/admin/manage-applications/new', name: 'dashboard_admin_application_new', methods: ['GET', 'POST'])]
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

            if ($this->security->isGranted(HasRoles::ADMIN)) {
                return $this->redirectToRoute('dashboard_admin_application_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('dashboard_creator_application_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('dashboard/shared/application/new.html.twig', compact('application', 'form'));
    }

    #[Route(path: '/creator/my-applications/token/{id}', name: 'dashboard_creator_application_token', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    #[Route(path: '/admin/manage-applications/token/{id}', name: 'dashboard_admin_application_token', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function token(
        Application $application,
        #[CurrentUser] ?User $user,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        if ($application->getUser() !== $user && !$this->security->isGranted(HasRoles::ADMIN)) {
            $this->addFlash('danger', $translator->trans('Application in you does not belong.'));

            if ($this->security->isGranted(HasRoles::ADMIN)) {
                return $this->redirectToRoute('dashboard_admin_application_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('dashboard_creator_application_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $length = 64;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $tokenPlain = '';
        for ($i = 0; $i < $length; ++$i) {
            $tokenPlain .= $characters[random_int(0, $charactersLength - 1)];
        }
        // crypter le token
        $salt = $this->getParameter('website_security_salt');
        $token = crypt($tokenPlain, $salt);

        $application->setToken($token);
        $em->persist($application);
        $em->flush();

        $this->addFlash('success', $translator->trans('Please note the following token : '.$tokenPlain."\n It will no longer be searchable."));

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            return $this->redirectToRoute('dashboard_admin_application_index', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->redirectToRoute('dashboard_creator_application_index', [], Response::HTTP_SEE_OTHER);
        }
    }
}
