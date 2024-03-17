<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Status;
use App\Form\StatusFormType;
use App\Entity\Traits\HasRoles;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/%website_dashboard_path%/main-panel/manage-status', name: 'dashboard_admin_status_')]
#[IsGranted(HasRoles::ADMIN)]
class StatusController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(StatusRepository $statusRepository): Response
    {
        $statuses = $statusRepository->findAll();

        return $this->render('dashboard/admin/status/index.html.twig', compact('statuses'));
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $status = new Status();
        $form = $this->createForm(StatusFormType::class, $status)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($status);
            $em->flush();

            $this->addFlash('success', $translator->trans('Status was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_status_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/status/new.html.twig', compact('status', 'form'));
    }
}
