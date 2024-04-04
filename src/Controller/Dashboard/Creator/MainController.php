<?php

namespace App\Controller\Dashboard\Creator;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/creator', name: 'dashboard_creator_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('dashboard_creator_orders');
    }
}
