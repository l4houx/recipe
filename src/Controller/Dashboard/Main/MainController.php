<?php

namespace App\Controller\Dashboard\Main;

use App\Entity\Traits\HasRoles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MainController extends AbstractController
{
    #[Route(path: '/%website_dashboard_path%', name: 'dashboard_main', methods: ['GET'])]
    public function mainDashboard(AuthorizationCheckerInterface $authChecker): Response
    {
        if ($authChecker->isGranted(HasRoles::ADMIN)) {
            return $this->redirectToRoute('dashboard_main_panel');
        } elseif ($authChecker->isGranted(HasRoles::MODERATOR)) {
            return $this->redirectToRoute('dashboard_main_panel');
        } elseif ($authChecker->isGranted(HasRoles::DEFAULT)) {
            return $this->redirectToRoute('dashboard_main_account');
        }

        return $this->redirectToRoute('login');
    }
}
