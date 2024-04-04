<?php

declare(strict_types=1);

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @method User getUser()
 */
#[Route(path: '/%website_dashboard_path%/my-invoices', name: 'dashboard_invoice_')]
#[IsGranted(HasRoles::DEFAULT)]
class InvoicesController extends BaseController
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUserOrThrow();
        $transactions = $this->transactionRepository->findfor($this->getUser());

        return $this->render('dashboard/shared/account/invoice/index.html.twig', compact('transactions', 'user'));
    }

    #[Route(path: '', methods: ['POST'])]
    public function edit(Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $content = (string) $request->request->get('invoiceInfo');

        $user = $this->getUserOrThrow();
        $user->setInvoiceInfo($content);

        $em->flush();

        $this->addFlash('success', $this->translator->trans('Your information has been saved successfully.'));

        return $this->redirectToRoute('dashboard_invoice_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $transaction = $this->transactionRepository->findOneBy([
            'id' => $id,
            'author' => $this->getUser(),
        ]);

        if (null === $transaction) {
            throw new NotFoundHttpException();
        }

        return $this->render('dashboard/shared/account/invoice/show.html.twig', compact('transaction'));
    }
}
