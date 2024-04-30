<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class ReportsController extends BaseController
{
    #[Route(path: '/admin/manage-reports', name: 'dashboard_admin_reports_index', methods: ['GET'])]
    #[Route(path: '/restaurant/my-reports', name: 'dashboard_restaurant_reports_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, SettingService $settingService): Response
    {
        $reference = '' == $request->query->get('reference') ? 'all' : $request->query->get('reference');
        $restaurant = '' == $request->query->get('restaurant') ? 'all' : $request->query->get('restaurant');
        $recipe = '' == $request->query->get('recipe') ? 'all' : $request->query->get('recipe');

        if ($this->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()?->getSlug();
        }

        $rows = $paginator->paginate($settingService->getRecipeDates(['reference' => $reference, 'restaurant' => $restaurant, 'recipe' => $recipe]), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/shared/reports/index.html.twig', compact('rows'));
    }
}
