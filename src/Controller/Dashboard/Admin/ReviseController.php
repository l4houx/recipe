<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Revise;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Event\Post\ReviseAcceptedEvent;
use App\Event\Post\ReviseRefusedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @method User getUser()
 */
#[Route('/%website_dashboard_path%/main-panel/manage-revises', name: 'dashboard_admin_revise_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class ReviseController extends AdminBaseController
{
    #[Route(path: '/{id<\d+>}', name: 'show', methods: ['GET', 'POST'])]
    public function show(Request $request, TranslatorInterface $translator, Revise $revise, EventDispatcherInterface $dispatcher): Response
    {
        if ('POST' === $request->getMethod()) {
            $isDeleteRequest = null !== $request->get('delete');

            if ($isDeleteRequest) {
                $dispatcher->dispatch(new ReviseRefusedEvent($revise, $request->get('comment')));

                $this->addFlash('danger', $translator->trans('Content was deleted successfully.'));
            } else {
                $revise->setContent($request->get('content'));
                $dispatcher->dispatch(new ReviseAcceptedEvent($revise));

                $this->addFlash('success', $translator->trans('Content was accepted successfully.'));
            }

            return $this->redirectToRoute('dashboard_main_panel', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/blog/revise.html.twig', compact('revise'));
    }
}
