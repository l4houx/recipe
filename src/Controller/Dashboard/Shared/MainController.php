<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\Traits\HasRoles;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/** MyProfile */
#[IsGranted(HasRoles::DEFAULT)]
class MainController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/account', name: 'dashboard_main_account', methods: ['GET'])]
    public function mainDashboard(
    ): Response {
        $user = $this->getUserOrThrow();

        return $this->render('dashboard/shared/main.html.twig', compact('user'));
    }
}
