<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Status;
use App\Form\StatusFormType;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/%website_dashboard_path%/admin/manage-status', name: 'dashboard_admin_status_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class StatusController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly StatusRepository $statusRepository,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(StatusRepository $statusRepository): Response
    {
        $rows = $statusRepository->findAll();

        return $this->render('dashboard/admin/status/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function newedit(Request $request, ?int $id = null): Response
    {
        if (!$id) {
            $status = new Status();
        } else {
            //$status = $this->statusRepository->find($id);
            $status = $this->settingService->getStatus(['id' => $id])->getQuery()->getOneOrNullResult();
            if (!$status) {
                $this->addFlash('danger', $this->translator->trans('The status can not be found'));

                return $this->redirectToRoute('dashboard_admin_status_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(StatusFormType::class, $status)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($status);
                $this->em->flush();
                if (!$id) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_status_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/status/new-edit.html.twig', compact('status', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Status $status): Response
    {
        if ($this->isCsrfTokenValid('status_deletion_'.$status->getId(), $request->request->get('_token'))) {
            $this->em->remove($status);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_status_index', [], Response::HTTP_SEE_OTHER);
    }
}
