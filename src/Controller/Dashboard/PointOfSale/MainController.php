<?php

namespace App\Controller\Dashboard\PointOfSale;

use App\Controller\BaseController;
use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/pointofsale', name: 'dashboard_pointofsale_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, SettingService $settingService): Response
    {
        $settingService->emptyCart($this->getUser());
        $rows = $paginator->paginate($settingService->getRecipes(['onsalebypos' => $this->getUser()->getPointofsale()])->getQuery(), $request->query->getInt('page', 1), 12, ['wrap-queries' => true]);

        return $this->render('dashboard/pointofsale/index.html.twig', compact('rows'));
    }
}
