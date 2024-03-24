<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/%website_dashboard_path%/main-panel/manage-pages', name: 'dashboard_admin_page_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class PagesController extends AdminBaseController
{

}
