<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Pricing;
use App\Entity\Traits\HasRoles;
use App\Form\PricingFormType;
use App\Repository\PricingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-pricings', name: 'dashboard_admin_pricing_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class PricingController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly PricingRepository $pricingRepository,
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $rows = $this->pricingRepository->findAll();

        return $this->render('dashboard/admin/pricing/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $pricing = new Pricing();
        $form = $this->createForm(PricingFormType::class, $pricing)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($pricing);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Content was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_pricing_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/pricing/new.html.twig', compact('pricing', 'form'));
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Request $request, Pricing $pricing): Response
    {
        $form = $this->createForm(PricingFormType::class, $pricing)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

            return $this->redirectToRoute('dashboard_admin_pricing_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/pricing/edit.html.twig', compact('pricing', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Pricing $pricing): Response
    {
        if ($this->isCsrfTokenValid('pricing_deletion_'.$pricing->getId(), $request->request->get('_token'))) {
            $this->em->remove($pricing);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_pricing_index', [], Response::HTTP_SEE_OTHER);
    }
}
