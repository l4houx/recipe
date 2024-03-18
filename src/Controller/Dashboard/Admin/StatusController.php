<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Status;
use App\Entity\Traits\HasRoles;
use App\Form\StatusFormType;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-status', name: 'dashboard_admin_status_')]
#[IsGranted(HasRoles::ADMIN)]
class StatusController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly StatusRepository $statusRepository,
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(StatusRepository $statusRepository): Response
    {
        $statuses = $statusRepository->findAll();

        return $this->render('dashboard/admin/status/index.html.twig', compact('statuses'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $status = new Status();
        $form = $this->createForm(StatusFormType::class, $status)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($status);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Status was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/status/new.html.twig', compact('status', 'form'));
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Request $request, Status $status): Response
    {
        $form = $this->createForm(StatusFormType::class, $status)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', $this->translator->trans('Status was edited successfully.'));

            return $this->redirectToRoute('dashboard_admin_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/status/edit.html.twig', compact('status', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Status $status): Response
    {
        if ($this->isCsrfTokenValid('status_deletion_'.$status->getId(), $request->request->get('_token'))) {
            $this->em->remove($status);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Status was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_status_index', [], Response::HTTP_SEE_OTHER);
    }
}
