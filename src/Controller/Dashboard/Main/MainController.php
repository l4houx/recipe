<?php

namespace App\Controller\Dashboard\Main;

use App\Entity\Traits\HasRoles;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MainController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%', name: 'dashboard_main', methods: ['GET'])]
    public function main(AuthorizationCheckerInterface $authChecker): Response
    {
        if ($authChecker->isGranted(HasRoles::TEAM)) {
            return $this->redirectToRoute('dashboard_admin_index');;
        } elseif ($authChecker->isGranted(HasRoles::RESTAURANT)) {
            return $this->redirectToRoute("dashboard_restaurant_index");
        } elseif ($authChecker->isGranted(HasRoles::CREATOR)) {
            return $this->redirectToRoute("dashboard_creator_index");
        } elseif ($authChecker->isGranted(HasRoles::DEFAULT)) {
            return $this->redirectToRoute('dashboard_creator_account_dashboard');
        } elseif ($authChecker->isGranted(HasRoles::SCANNER)) {
            return $this->redirectToRoute("dashboard_scanner_index");
        } elseif ($authChecker->isGranted(HasRoles::POINTOFSALE)) {
            return $this->redirectToRoute("dashboard_pointofsale_index");
        }

        return $this->redirectToRoute('login');
    }
}
