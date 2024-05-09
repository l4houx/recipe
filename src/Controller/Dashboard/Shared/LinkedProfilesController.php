<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(HasRoles::DEFAULT)]
class LinkedProfilesController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/linked-profiles', name: 'dashboard_linked_profiles', methods: ['GET', 'POST'])]
    public function linkedProfiles(Request $request): Response
    {
        $user = $this->getUserOrThrow();

        return $this->render('dashboard/shared/auth/linked-profiles.html.twig', compact('user'));
    }
}
