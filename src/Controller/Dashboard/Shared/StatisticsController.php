<?php

namespace App\Controller\Dashboard\Shared;

use App\Entity\RecipeDate;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Controller\BaseController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class StatisticsController extends BaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService,
        private readonly AuthorizationCheckerInterface $authChecker
    ) {
    }

    #[Route(path: '/admin/manage-recipes/{recipeSlug}/recipe-dates/{recipeDateReference}/statistics', name: 'dashboard_admin_recipe_date_statistics_index', methods: ['GET'], requirements: ['recipeSlug' => Requirement::ASCII_SLUG, 'recipeDateReference' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-recipes/{recipeSlug}/recipe-dates/{recipeDateReference}/statistics', name: 'dashboard_restaurant_recipe_date_statistics_index', methods: ['GET'], requirements: ['recipeSlug' => Requirement::ASCII_SLUG, 'recipeDateReference' => Requirement::ASCII_SLUG])]
    public function recipeDateStatistics(string $recipeDateReference)
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()?->getSlug();
        }

        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['reference' => $recipeDateReference, 'restaurant' => $restaurant])->getQuery()->getOneOrNullResult();

        if (!$recipeDate) {
            $this->addFlash('danger', $this->translator->trans('The recipe date can not be found'));

            return $this->settingService->redirectToReferer('recipe');
        }

        // Subscriptions Sold By Channel Pie Chart
        $subscriptionsSoldByChannelPieChart = new PieChart();
        $subscriptionsSoldByChannelPieChart->getData()->setArrayToDataTable(
                [[$this->translator->trans("Sales channel"), $this->translator->trans("Number of subscriptions")],
                    [$this->translator->trans("Online"), $recipeDate->getOrderElementsQuantitySum(1, "all", HasRoles::POINTOFSALE)],
                    [$this->translator->trans("POS"), $recipeDate->getOrderElementsQuantitySum(1, "all", HasRoles::POINTOFSALE)],
                ]
        );
        $subscriptionsSoldByChannelPieChart->getOptions()->setTitle($this->translator->trans("Subscriptions sold by channel"));
        $subscriptionsSoldByChannelPieChart->getOptions()->setIs3D(true);
        $subscriptionsSoldByChannelPieChart->getOptions()->setHeight(200);
        $subscriptionsSoldByChannelPieChart->getOptions()->setWidth(200);
        $subscriptionsSoldByChannelPieChart->getOptions()->getTitleTextStyle()->setBold(false);
        $subscriptionsSoldByChannelPieChart->getOptions()->getTitleTextStyle()->setColor('#6c757d');
        $subscriptionsSoldByChannelPieChart->getOptions()->getTitleTextStyle()->setFontSize(9);
        $subscriptionsSoldByChannelPieChart->getOptions()->getLegend()->setPosition("bottom");

        // Gross Sales By Channel Pie Chart
        $grossSalesByChannelPieChart = new PieChart();
        $grossSalesByChannelPieChart->getData()->setArrayToDataTable(
                [[$this->translator->trans("Sales channel"), $this->translator->trans("Gross Sales")],
                    [$this->translator->trans("Online"), $recipeDate->getSales(HasRoles::CREATOR)],
                    [$this->translator->trans("POS"), $recipeDate->getSales(HasRoles::POINTOFSALE)],
                ]
        );
        $grossSalesByChannelPieChart->getOptions()->setTitle($this->translator->trans("Gross sales by channel") . " (" . $this->settingService->getSettings("currency_ccy") . ")");
        $grossSalesByChannelPieChart->getOptions()->setIs3D(true);
        $grossSalesByChannelPieChart->getOptions()->setHeight(200);
        $grossSalesByChannelPieChart->getOptions()->setWidth(200);
        $grossSalesByChannelPieChart->getOptions()->getTitleTextStyle()->setBold(false);
        $grossSalesByChannelPieChart->getOptions()->getTitleTextStyle()->setColor('#6c757d');
        $grossSalesByChannelPieChart->getOptions()->getTitleTextStyle()->setFontSize(9);
        $grossSalesByChannelPieChart->getOptions()->getLegend()->setPosition("bottom");

        // Point of sales stats
        $subscriptionsSoldPerPointOfSalePieChart = null;
        $grossSalesPerPointOfSalePieChart = null;

        if ($recipeDate->getOrderElementsQuantitySum(1, "all", HasRoles::POINTOFSALE) > 0) {
            foreach ($recipeDate->getPointofsales() as $pointOfSale) {
                $subscriptionsSoldPerPointOfSale[] = [$pointOfSale->getName(), $recipeDate->getOrderElementsQuantitySum(1, $pointOfSale->getUser())];
                $grossSalesPerPointOfSale[] = [$pointOfSale->getName(), $recipeDate->getSales(HasRoles::POINTOFSALE, $pointOfSale->getUser())];
            }

            // Subscriptions sold Per Point of sale
            array_unshift($subscriptionsSoldPerPointOfSale, [$this->translator->trans("Point of sale"), $this->translator->trans("Subscriptions sold")]);
            $subscriptionsSoldPerPointOfSalePieChart = new PieChart();
            $subscriptionsSoldPerPointOfSalePieChart->getData()->setArrayToDataTable($subscriptionsSoldPerPointOfSale);
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->setTitle($this->translator->trans("Subscriptions sold Per Point of sale"));
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->setIs3D(true);
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->setHeight(200);
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->setWidth(200);
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->getTitleTextStyle()->setBold(false);
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->getTitleTextStyle()->setColor('#6c757d');
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->getTitleTextStyle()->setFontSize(9);
            $subscriptionsSoldPerPointOfSalePieChart->getOptions()->getLegend()->setPosition("bottom");

            // Gross Sales Per Point of sale
            array_unshift($grossSalesPerPointOfSale, [$this->translator->trans("Point of sale"), $this->translator->trans("Gross sales")]);
            $grossSalesPerPointOfSalePieChart = new PieChart();
            $grossSalesPerPointOfSalePieChart->getData()->setArrayToDataTable($grossSalesPerPointOfSale);
            $grossSalesPerPointOfSalePieChart->getOptions()->setTitle($this->translator->trans("Gross sales per Point of sale") . " (" . $this->settingService->getSettings("currency_ccy") . ")");
            $grossSalesPerPointOfSalePieChart->getOptions()->setIs3D(true);
            $grossSalesPerPointOfSalePieChart->getOptions()->setHeight(200);
            $grossSalesPerPointOfSalePieChart->getOptions()->setWidth(200);
            $grossSalesPerPointOfSalePieChart->getOptions()->getTitleTextStyle()->setBold(false);
            $grossSalesPerPointOfSalePieChart->getOptions()->getTitleTextStyle()->setColor('#6c757d');
            $grossSalesPerPointOfSalePieChart->getOptions()->getTitleTextStyle()->setFontSize(9);
            $grossSalesPerPointOfSalePieChart->getOptions()->getLegend()->setPosition("bottom");
        }

        // Subscriptions Sales By Date Line Chart
        $ordersQuantityByDate = $this->settingService->getOrders(array("recipedate" => $recipeDateReference, "ordersQuantityByDateStat" => true, "order" => "ASC"))->getQuery()->getResult();

        foreach ($ordersQuantityByDate as $i => $resultArray) {
            $ordersQuantityByDate[$i] = array_values($resultArray);
            $ordersQuantityByDate[$i][1] = \DateTime::createFromFormat('Y-m-j', $ordersQuantityByDate[$i][1]);
            $ordersQuantityByDate[$i] = array_reverse($ordersQuantityByDate[$i]);
        }

        array_unshift($ordersQuantityByDate, [['label' => $this->translator->trans("Date"), 'type' => 'date'], ['label' => $this->translator->trans("Subscriptions sold"), 'type' => 'number']]);
        $subscriptionsSalesByDateLineChart = new LineChart();
        $subscriptionsSalesByDateLineChart->getData()->setArrayToDataTable($ordersQuantityByDate);
        $subscriptionsSalesByDateLineChart->getOptions()->setTitle($this->translator->trans("Subscriptions sales by date"));
        $subscriptionsSalesByDateLineChart->getOptions()->setCurveType('function');
        $subscriptionsSalesByDateLineChart->getOptions()->setLineWidth(2);
        $subscriptionsSalesByDateLineChart->getOptions()->getLegend()->setPosition('none');

        // Individual Subscriptions Sold By Channel Pie Charts
        $subscriptionsSoldByChannelPieChartsArray = array();
        $subscriptionsGrossSalesByChannelPieChartsArray = array();

        foreach ($recipeDate->getSubscriptions() as $recipeSubscription) {
            $thisSubscriptionsSoldByChannelPieChart = new PieChart();
            $thisSubscriptionsSoldByChannelPieChart->getData()->setArrayToDataTable(
                    [[$this->translator->trans("Sales channel"), $this->translator->trans("Number of subscriptions")],
                        [$this->translator->trans("Online"), $recipeSubscription->getOrderElementsQuantitySum(1, "all", HasRoles::CREATOR)],
                        [$this->translator->trans("POS"), $recipeSubscription->getOrderElementsQuantitySum(1, "all", HasRoles::POINTOFSALE)],
                    ]
            );
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->setTitle($this->translator->trans("Subscriptions sold by channel"));
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->setIs3D(true);
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->setHeight(200);
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->setWidth(200);
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->getTitleTextStyle()->setBold(false);
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->getTitleTextStyle()->setColor('#6c757d');
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->getTitleTextStyle()->setFontSize(9);
            $thisSubscriptionsSoldByChannelPieChart->getOptions()->getLegend()->setPosition("bottom");

            $thisGrossSalesByChannelPieChart = new PieChart();
            $thisGrossSalesByChannelPieChart->getData()->setArrayToDataTable(
                    [[$this->translator->trans("Sales channel"), $this->translator->trans("Gross Sales")],
                        [$this->translator->trans("Online"), $recipeSubscription->getSales(HasRoles::CREATOR)],
                        [$this->translator->trans("POS"), $recipeSubscription->getSales(HasRoles::POINTOFSALE)],
                    ]
            );
            $thisGrossSalesByChannelPieChart->getOptions()->setTitle($this->translator->trans("Gross sales by channel") . " (" . $this->settingService->getSettings("currency_ccy") . ")");
            $thisGrossSalesByChannelPieChart->getOptions()->setIs3D(true);
            $thisGrossSalesByChannelPieChart->getOptions()->setHeight(200);
            $thisGrossSalesByChannelPieChart->getOptions()->setWidth(200);
            $thisGrossSalesByChannelPieChart->getOptions()->getTitleTextStyle()->setBold(false);
            $thisGrossSalesByChannelPieChart->getOptions()->getTitleTextStyle()->setColor('#6c757d');
            $thisGrossSalesByChannelPieChart->getOptions()->getTitleTextStyle()->setFontSize(9);
            $thisGrossSalesByChannelPieChart->getOptions()->getLegend()->setPosition("bottom");

            array_push($subscriptionsSoldByChannelPieChartsArray, $thisSubscriptionsSoldByChannelPieChart);
            array_push($subscriptionsGrossSalesByChannelPieChartsArray, $thisGrossSalesByChannelPieChart);
        }

        return $this->render('dashboard/shared/statistics/recipe-date.html.twig', compact(
            'recipeDate', 
            'subscriptionsSoldByChannelPieChart', 
            'grossSalesByChannelPieChart',
            'subscriptionsSoldPerPointOfSalePieChart',
            'grossSalesPerPointOfSalePieChart',
            'subscriptionsSalesByDateLineChart',
            'subscriptionsSoldByChannelPieChartsArray',
            'subscriptionsGrossSalesByChannelPieChartsArray'
        ));
    }

    #[Route(path: '/admin/manage-recipes/{recipeSlug}/recipe-dates/{recipeDateReference}/reserved-seats', name: 'dashboard_admin_recipe_date_statistics_reservedSeats', methods: ['GET'], requirements: ['recipeSlug' => Requirement::ASCII_SLUG, 'recipeDateReference' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-recipes/{recipeSlug}/recipe-dates/{recipeDateReference}/reserved-seats', name: 'dashboard_restaurant_recipe_date_statistics_reservedSeats', methods: ['GET'], requirements: ['recipeSlug' => Requirement::ASCII_SLUG, 'recipeDateReference' => Requirement::ASCII_SLUG])]
    public function recipeDateReservedSeats(string $recipeDateReference)
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()?->getSlug();
        }

        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['reference' => $recipeDateReference, 'restaurant' => $restaurant])->getQuery()->getOneOrNullResult();

        if (!$recipeDate) {
            $this->addFlash('danger', $this->translator->trans('The recipe date can not be found'));

            return $this->settingService->redirectToReferer('recipe');
        }

        return $this->render('dashboard/shared/statistics/reserved-seats.html.twig', compact('recipeDate'));
    }
}
