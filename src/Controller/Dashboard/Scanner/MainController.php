<?php

namespace App\Controller\Dashboard\Scanner;

use App\Controller\BaseController;
use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/scanner', name: 'dashboard_scanner_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, SettingService $settingService): Response
    {
        $rows = $paginator->paginate($settingService->getRecipes(['canbescannedby' => $this->getUser()->getScanner()])->getQuery(), $request->query->getInt('page', 1), 12, ['wrap-queries' => true]);

        return $this->render('dashboard/scanner/index.html.twig', compact('rows'));
    }
}
