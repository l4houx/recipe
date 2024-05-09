<?php

namespace App\Controller\Dashboard\Restaurant;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(HasRoles::RESTAURANT)]
class MainController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/restaurant', name: 'dashboard_restaurant_index', methods: ['GET'])]
    public function index(SettingService $settingService, TranslatorInterface $translator): Response
    {
        // Subscriptions Sales By Date Line Chart
        $datefrom = date_format(new \DateTime(), 'Y-m-01');
        $dateto = date_format(new \DateTime(), 'Y-m-t');

        $ordersQuantityByDate = $settingService->getOrders(['restaurant' => $this->getUser()->getRestaurant()?->getSlug(), 'ordersQuantityByDateStat' => true, 'order' => 'ASC', 'datefrom' => $datefrom, 'dateto' => $dateto])->getQuery()->getResult();

        foreach ($ordersQuantityByDate as $i => $resultArray) {
            $ordersQuantityByDate[$i] = array_values($resultArray);
            $ordersQuantityByDate[$i][1] = \DateTime::createFromFormat('Y-m-j', $ordersQuantityByDate[$i][1]);
            $ordersQuantityByDate[$i] = array_reverse($ordersQuantityByDate[$i]);
        }
        array_unshift($ordersQuantityByDate, [['label' => $translator->trans('Date'), 'type' => 'date'], ['label' => $translator->trans('Subscriptions sold'), 'type' => 'number']]);
        $subscriptionsSalesByDateLineChart = new LineChart();
        $subscriptionsSalesByDateLineChart->getData()->setArrayToDataTable($ordersQuantityByDate);
        $subscriptionsSalesByDateLineChart->getOptions()->setTitle($translator->trans('Subscriptions sales this month'));
        $subscriptionsSalesByDateLineChart->getOptions()->setCurveType('function');
        $subscriptionsSalesByDateLineChart->getOptions()->setLineWidth(2);
        $subscriptionsSalesByDateLineChart->getOptions()->getLegend()->setPosition('none');

        return $this->render('dashboard/restaurant/index.html.twig', compact('subscriptionsSalesByDateLineChart'));
    }
}
