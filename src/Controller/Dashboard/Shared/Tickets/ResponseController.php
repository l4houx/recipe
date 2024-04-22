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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted(HasRoles::DEFAULT)]
#[Route(path: '/%website_dashboard_path%/my-responses', name: 'dashboard_response_')]
class ResponseController extends BaseController
{
    #[Route(path: '/{id}', name: 'index', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function index(
        Request $request,
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

                return $this->redirectToRoute('dashboard_response_index', ['id' => $ticket->getId()]);
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        $responses = $responseRepository->findBy(['ticket' => $ticket]);

        return $this->render('dashboard/shared/tickets/response.html.twig', compact('responses', 'ticket', 'form'));
    }
}
