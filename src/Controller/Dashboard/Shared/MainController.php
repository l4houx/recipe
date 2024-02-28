<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** MyProfile */
#[IsGranted(HasRoles::DEFAULT)]
class MainController extends Controller
{
    #[Route(path: '/%website_dashboard_path%/account', name: 'dashboard_main_account', methods: ['GET'])]
    public function mainDashboard(): Response
    {
        $user = $this->getUserOrThrow();

        return $this->render('dashboard/shared/main.html.twig', compact('user'));
    }
}
