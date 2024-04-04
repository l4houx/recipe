<?php

namespace App\Controller\Dashboard\Admin;

use App\Form\CurrencyFormType;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Entity\Setting\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/%website_dashboard_path%/admin/manage-settings', name: 'dashboard_admin_setting_currency_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class CurrencyController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/currencies', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $ccy = ($request->query->get('ccy')) == "" ? "all" : $request->query->get('ccy');
        $symbol = ($request->query->get('symbol')) == "" ? "all" : $request->query->get('symbol');

        $rows = $paginator->paginate($this->settingService->getCurrencies(['ccy' => $ccy, 'symbol' => $symbol]), $request->query->getInt('page', 1), 20, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/setting/currency/index.html.twig', compact('rows'));
    }

    #[Route(path: '/currencies/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/currencies/{ccy}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $ccy = null): Response
    {
        if (!$ccy) {
            $currency = new Currency();
        } else {
            /** @var Currency $currency */
            $currency = $this->settingService->getCurrencies(['ccy' => $ccy])->getQuery()->getOneOrNullResult();
            if (!$currency) {
                $this->addFlash('danger', $this->translator->trans('The currency can not be found'));

                return $this->redirectToRoute('dashboard_admin_currency_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(CurrencyFormType::class, $currency)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($currency);
                $this->em->flush();

                if (!$ccy) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_currency_index', [], Response::HTTP_SEE_OTHER);
            }
            $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
        }

        return $this->render('dashboard/admin/setting/currency/new-edit.html.twig', compact('form', 'currency'));
    }

    #[Route(path: '/currencies/{ccy}/delete', name: 'delete', methods: ['POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(Request $request, Currency $currency, string $ccy): Response
    {
        $currency = $this->settingService->getCurrencies(['ccy' => $ccy])->getQuery()->getOneOrNullResult();
        if (!$currency) {
            $this->addFlash('danger', $this->translator->trans('The currency can not be found'));

            return $this->redirectToRoute('dashboard_admin_currency_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('currency_deletion_'.$currency->getId(), $request->request->get('_token'))) {
            $this->em->remove($currency);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_currency_index', [], Response::HTTP_SEE_OTHER);
    }
}
