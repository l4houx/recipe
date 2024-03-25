<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Setting\Currency;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/main-panel/manage-settings', name: 'dashboard_admin_currency_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class CurrencyController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/payment', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $ccy = '' === $request->query->get('ccy') ? 'all' : $request->query->get('ccy');
        $symbol = '' === $request->query->get('symbol') ? 'all' : $request->query->get('symbol');

        $rows = $paginator->paginate($this->settingService->getCurrencies(['ccy' => $ccy, 'symbol' => $symbol]), $request->query->getInt('page', 1), 20, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/setting/currency/index.html.twig', compact('rows'));
    }

    #[Route(path: '/payment/add', name: 'add', methods: ['GET', 'POST'])]
    #[Route(path: '/payment/{ccy}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function addedit(Request $request, ?string $ccy = null): Response
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

        $form = $this->createForm(CurrencyType::class, $currency)->handleRequest($request);

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

        return $this->render('dashboard/admin/setting/currency/add-edit.html.twig', compact('form', 'currency'));
    }

    #[Route(path: '/payment/{ccy}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Currency $currency, string $ccy): Response
    {
        $currency = $this->settingService->getCurrencies(['ccy' => $ccy])->getQuery()->getOneOrNullResult();
        if (!$currency) {
            $this->addFlash('danger', $this->translator->trans('The currency can not be found'));

            return $this->redirectToRoute('dashboard_admin_currency_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('danger', $this->translator->trans('The currency has been deleted'));

        $this->em->remove($currency);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_currency_index', [], Response::HTTP_SEE_OTHER);
    }
}
