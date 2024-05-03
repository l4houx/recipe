<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Transaction;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Traits\HasRoles;
use App\Infrastructural\Payment\Payment;
use App\Repository\TransactionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Infrastructural\Payment\Event\PaymentRefundedEvent;

#[Route('/%website_dashboard_path%/admin/manage-transactions', name: 'dashboard_admin_transaction_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class TransactionsController extends AdminBaseController
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $query = $this->transactionRepository->findBy([], ['createdAt' => 'DESC']);
        $page = $request->query->getInt('page', 1);

        $rows = $paginator->paginate(
            $query,
            $page,
            10,
            ['wrap-queries' => true]
        );

        return $this->render('dashboard/admin/transactions/index.html.twig', compact('rows'));
    }

    #[Route(path: '/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    public function delete(Transaction $transaction, TranslatorInterface $translator, EventDispatcherInterface $dispatcher): Response
    {
        $payment = new Payment();
        $payment->id = (string) $transaction->getMethodRef();
        $dispatcher->dispatch(new PaymentRefundedEvent($payment));

        $this->addFlash('success', $translator->trans('The payment has been marked as refunded'));

        return $this->redirectBack('dashboard_admin_transaction_index');
    }

    #[Route(path: '/report', name: 'report', methods: ['GET'])]
    public function report(Request $request): Response
    {
        $year = $request->query->getInt('year', (int) date('Y'));

        return $this->render('dashboard/admin/transactions/report.html.twig', [
            'rows' => $this->transactionRepository->getMonthlyReport($year),
            'prefix' => 'admin_transaction',
            'current_year' => date('Y'),
            'year' => $year,
        ]);
    }

    public function applySearch(string $search, QueryBuilder $query): QueryBuilder
    {
        $query = $query->leftJoin('row.author', 'u');

        if (str_starts_with($search, 'user:')) {
            return $query
                ->where('u.id = :search')
                ->setParameter('search', str_replace('user:', '', $search))
            ;
        }

        return $query->where('row.methodRef = :search')
            ->orWhere('u.email = :search')
            ->setParameter('search', $search)
        ;
    }
}
