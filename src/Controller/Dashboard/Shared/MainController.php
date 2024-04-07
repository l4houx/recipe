<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Repository\ApplicationRepository;
use App\Repository\LevelRepository;
use App\Repository\StatusRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** My Profile Creator */
#[Route(path: '/%website_dashboard_path%/creator', name: 'dashboard_creator_')]
#[IsGranted(HasRoles::CREATOR)]
class MainController extends BaseController
{
    #[Route(path: '/account-dashboard', name: 'account_dashboard', methods: ['GET'])]
    public function dashboard(
        #[CurrentUser] ?User $user,
        Security $security,
        TicketRepository $ticketRepository,
        LevelRepository $levelRepository,
        StatusRepository $statusRepository,
        ApplicationRepository $applicationRepository
    ): Response {
        $levels = $levelRepository->findAll();
        $statuses = $statusRepository->findAll();

        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        if ($security->isGranted(HasRoles::ADMIN)) {
            $tickets = $ticketRepository->findAll();
        } else {
            $applications = $applicationRepository->findBy(['user' => $user->getId()]);
            $ticketsApp = [];
            foreach ($applications as $application) {
                $ticketsApp = array_merge($ticketsApp, $application->getTickets()->toArray());
            }
            $ticketsUser = $ticketRepository->findBy(['user' => $user->getId()]);
            $tickets = array_merge($ticketsApp, $ticketsUser);
        }

        return $this->render('dashboard/shared/dashboard.html.twig', compact('user', 'tickets', 'statuses', 'levels'));
    }
}
