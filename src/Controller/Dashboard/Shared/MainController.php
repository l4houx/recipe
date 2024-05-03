<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\User;
use App\Entity\Traits\HasRoles;
use App\Controller\BaseController;
use App\Repository\LevelRepository;
use App\Repository\RecipeRepository;
use App\Repository\ReviseRepository;
use App\Repository\StatusRepository;
use App\Repository\TicketRepository;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/** My Profile Creator */
#[Route(path: '/%website_dashboard_path%/creator', name: 'dashboard_creator_')]
#[IsGranted(HasRoles::CREATOR)]
class MainController extends BaseController
{
    #[Route(path: '/account-dashboard', name: 'account_dashboard', methods: ['GET'])]
    public function dashboard(
        #[CurrentUser] ?User $user,
        Security $security,
        RecipeRepository $recipeRepository,
        ReviseRepository $reviseRepository,
        TicketRepository $ticketRepository,
        LevelRepository $levelRepository,
        StatusRepository $statusRepository,
        ApplicationRepository $applicationRepository
    ): Response {
        $user = $this->getUserOrThrow();

        //$lastRecipes = $recipeRepository->findLastByUser($user, 6);
        $lastRevises = $reviseRepository->findPendingFor($user);
        $lastLevels = $levelRepository->findAll();
        $lastStatuses = $statusRepository->findAll();

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

        $hasActivity = !empty($lastRecipes) || !empty($lastRevises) || !empty($lastLevels) || !empty($lastStatuses);

        return $this->render('dashboard/shared/dashboard.html.twig', compact('user', 'hasActivity', 'lastRevises'/*, 'lastRecipes'*/, 'lastLevels', 'lastStatuses'));
    }
}
