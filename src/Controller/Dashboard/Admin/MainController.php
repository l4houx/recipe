<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use App\Infrastructural\Mail\Mail;
use App\Repository\RecipeRepository;
use App\Repository\ReportRepository;
use App\Repository\ReviseRepository;
use App\Repository\CommentRepository;
use App\Repository\TransactionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(HasRoles::TEAM)]
class MainController extends AdminBaseController
{
    #[Route(path: '/%website_dashboard_path%/main-panel', name: 'dashboard_main_panel', methods: ['GET'])]
    public function mainDashboard(
        PaginatorInterface $paginator,
        ReviseRepository $reviseRepository,
        RecipeRepository $recipeRepository,
        CommentRepository $commentRepository,
        ReportRepository $reportRepository,
        TransactionRepository $transactionRepository
    ): Response {
        return $this->render('dashboard/admin/main.html.twig', [
            'revises' => $reviseRepository->findLatest(10),
            'recipes' => $recipeRepository->findLatest(4),
            'comments' => $paginator->paginate($commentRepository->queryLatest(6)),
            'reports' => $reportRepository->findAll(),
            'months' => $transactionRepository->getMonthlyRevenues(),
            'days' => $transactionRepository->getDailyRevenues(),
        ]);
    }

    #[Route(path: '/%website_dashboard_path%/main-panel/manage-stats', name: 'dashboard_admin_stats', methods: ['GET'])]
    public function stats(): Response
    {
        return $this->render('dashboard/admin/pages/stats.html.twig');
    }

    #[Route(path: '/%website_dashboard_path%/main-panel/manage-testmail', name: 'dashboard_admin_testmail', methods: ['POST'])]
    public function testMail(Request $request, TranslatorInterface $translator, Mail $mail): RedirectResponse
    {
        $email = $mail->sendEmail('mail/security/register.html.twig', [
            'user' => $this->getUserOrThrow(),
        ])
            ->to($request->get('email'))
            ->subject($this->getParameter('website_name').$translator->trans(' | Account Confirmation'))
        ;

        $mail->sendNow($email);

        $this->addFlash('success', $translator->trans('The test email was sent successfully'));

        return $this->redirectToRoute('dashboard_main_panel', [], Response::HTTP_SEE_OTHER);
    }
}
