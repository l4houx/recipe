<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use App\Infrastructural\Mail\Mail;
use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use App\Repository\ReportRepository;
use App\Repository\ReviseRepository;
use App\Repository\TransactionRepository;
use App\Service\SettingService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(HasRoles::TEAM)]
class MainController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/%website_dashboard_path%/admin', name: 'dashboard_admin_index', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator,
        ReviseRepository $reviseRepository,
        RecipeRepository $recipeRepository,
        // CommentRepository $commentRepository,
        ReportRepository $reportRepository,
        TransactionRepository $transactionRepository
    ): Response {
        // Subscriptions Sales By Date Line Chart
        /*
        $datefrom = date_format(new \DateTime(), 'Y-m-01');
        $dateto = date_format(new \DateTime(), 'Y-m-t');

        $ordersQuantityByDate = $this->settingService->getOrders(['ordersQuantityByDateStat' => true, 'order' => 'ASC', 'datefrom' => $datefrom, 'dateto' => $dateto])->getQuery()->getResult();

        foreach ($ordersQuantityByDate as $i => $resultArray) {
            $ordersQuantityByDate[$i] = array_values($resultArray);
            $ordersQuantityByDate[$i][1] = \DateTime::createFromFormat('Y-m-j', $ordersQuantityByDate[$i][1]);
            $ordersQuantityByDate[$i] = array_reverse($ordersQuantityByDate[$i]);
        }
        array_unshift($ordersQuantityByDate, [['label' => $this->translator->trans('Date'), 'type' => 'date'], ['label' => $this->translator->trans('Subscriptions sold'), 'type' => 'number']]);

        $subscriptionsSalesByDateLineChart = new LineChart();
        $subscriptionsSalesByDateLineChart->getData()->setArrayToDataTable($ordersQuantityByDate);
        $subscriptionsSalesByDateLineChart->getOptions()->setTitle($this->translator->trans('Subscriptions sales this month'));
        $subscriptionsSalesByDateLineChart->getOptions()->setCurveType('function');
        $subscriptionsSalesByDateLineChart->getOptions()->setLineWidth(2);
        $subscriptionsSalesByDateLineChart->getOptions()->getLegend()->setPosition('none');
        */

        return $this->render('dashboard/admin/index.html.twig', [
            'revises' => $reviseRepository->findLatest(10),
            'recipes' => $recipeRepository->findLatest(4),
            // 'comments' => $paginator->paginate($commentRepository->queryLatest(6)),
            'reports' => $reportRepository->findAll(),
            'months' => $transactionRepository->getMonthlyRevenues(),
            'days' => $transactionRepository->getDailyRevenues(),
            // 'subscriptionsSalesByDateLineChart' => $subscriptionsSalesByDateLineChart,
        ]);
    }

    #[Route(path: '/%website_dashboard_path%/admin/manage-stats', name: 'dashboard_admin_stats', methods: ['GET'])]
    public function stats(): Response
    {
        return $this->render('dashboard/admin/pages/stats.html.twig');
    }

    #[Route(path: '/%website_dashboard_path%/admin/manage-testmail', name: 'dashboard_admin_testmail', methods: ['POST'])]
    public function testMail(Request $request, Mail $mail): RedirectResponse
    {
        $email = $mail->sendEmail('mail/security/register.html.twig', [
            'user' => $this->getUserOrThrow(),
        ])
            ->to($request->get('email'))
            ->subject($this->getParameter('website_name').' | '.$this->translator->trans('Account Confirmation'))
        ;

        $mail->sendNow($email);

        $this->addFlash('success', $this->translator->trans('The test email was sent successfully'));

        return $this->redirectToRoute('dashboard_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
