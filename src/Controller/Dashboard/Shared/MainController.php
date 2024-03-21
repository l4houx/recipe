<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\Traits\HasRoles;
use App\Controller\BaseController;
use App\Controller\ReviseController;
use App\Repository\RecipeRepository;
use App\Repository\ReviseRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MyProfile */
#[IsGranted(HasRoles::DEFAULT)]
class MainController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/account', name: 'dashboard_main_account', methods: ['GET'])]
    public function mainDashboard(
        RecipeRepository $recipeRepository,
        ReviseRepository $reviseRepository
    ): Response {
        $user = $this->getUserOrThrow();

        $revises = $reviseRepository->findPendingFor($user);
        $lastRecipes = $recipeRepository->findLastByUser($user, 6);

        $hasActivity = !empty($lastRecipes) || !empty($revises);

        return $this->render('dashboard/shared/main.html.twig', compact('user', 'hasActivity', 'revises', 'lastRecipes'));
    }
}
