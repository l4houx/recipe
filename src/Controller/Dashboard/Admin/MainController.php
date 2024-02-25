<?php

namespace App\Controller\Dashboard\Admin;

use App\Controller\Controller;
use App\Entity\Traits\HasRoles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//#[IsGranted(HasRoles::MODERATOR)]
class MainController extends AbstractController
{
    #[Route(path: '/%website_dashboard_path%/main-panel', name: 'dashboard_main_panel', methods: [Request::METHOD_GET])]
    public function mainDashboard(): Response
    {
        return $this->render('dashboard/admin/main.html.twig');
    }
}
