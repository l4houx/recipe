<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/%website_dashboard_path%/admin/manage-translations', name: 'dashboard_admin_translation_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class TranslationController extends AdminBaseController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('dashboard/admin/translation/index.html.twig');
    }
}
