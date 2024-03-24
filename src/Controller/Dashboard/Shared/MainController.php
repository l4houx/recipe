<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\User;
use App\Entity\Traits\HasRoles;
use App\Controller\BaseController;
use App\Repository\LevelRepository;
use App\Repository\StatusRepository;
use App\Repository\TicketRepository;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/** MyProfile */
#[IsGranted(HasRoles::DEFAULT)]
class MainController extends BaseController
{
    public function __construct(private readonly Security $security)
    {
    }

    #[Route(path: '/%website_dashboard_path%/account', name: 'dashboard_main_account', methods: ['GET'])]
    public function mainDashboard(
        //#[CurrentUser] ?User $user,
        TicketRepository $ticketRepository,
        LevelRepository $levelRepository,
        StatusRepository $statusRepository,
        ApplicationRepository $applicationRepository
    ): Response {
        $user = $this->getUserOrThrow();

        $levels = $levelRepository->findAll();
        $statuses = $statusRepository->findAll();

        if ($user === null) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->security->isGranted(HasRoles::ADMIN)) {
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

        return $this->render('dashboard/shared/main.html.twig', compact('user', 'tickets', 'statuses', 'levels'));
    }
}
