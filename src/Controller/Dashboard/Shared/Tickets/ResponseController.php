<?php

namespace App\Controller\Dashboard\Shared\Tickets;

use App\Entity\User;
use App\Entity\Ticket;
use App\Form\ResponseFormType;
use App\Entity\Traits\HasRoles;
use App\Controller\BaseController;
use App\Repository\ResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Response as EntityResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted(HasRoles::DEFAULT)]
#[Route(path: '/%website_dashboard_path%')]
class ResponseController extends BaseController
{
    #[Route(path: '/creator/my-responses/{id}', name: 'dashboard_creator_response_index', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    #[Route(path: '/admin/manage-responses/{id}', name: 'dashboard_admin_response_index', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function index(
        Request $request,
        Security $security,
        Ticket $ticket,
        TranslatorInterface $translator,
        ResponseRepository $responseRepository,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): Response {
        $response = new EntityResponse();
        $form = $this->createForm(ResponseFormType::class, $response)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $response->setTicket($ticket);
                $response->setUser($user);

                $em->persist($response);
                $em->flush();

                $this->addFlash('success', $translator->trans('Answer was created successfully.'));

                if ($security->isGranted(HasRoles::ADMIN)) {
                    return $this->redirectToRoute('dashboard_admin_response_index', ['id' => $ticket->getId()]);
                } else {
                    return $this->redirectToRoute('dashboard_creator_response_index', ['id' => $ticket->getId()]);
                }
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        $responses = $responseRepository->findBy(['ticket' => $ticket]);

        return $this->render('dashboard/shared/tickets/response.html.twig', compact('responses', 'ticket', 'form'));
    }
}
