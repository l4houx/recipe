<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Content;
use App\Entity\Recipe;
use App\Entity\Traits\HasRoles;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/%website_dashboard_path%/admin/manage-contents', name: 'dashboard_admin_content_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class ContentController extends AdminBaseController
{
    #[Route(path: '/{id<\d+>}/title', name: 'title')]
    public function title(Content $content): JsonResponse
    {
        return new JsonResponse([
            'id' => $content->getId(),
            'title' => $content->getTitle(),
        ]);
    }
}
