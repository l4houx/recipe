<?php

namespace App\Controller\Dashboard\Shared\Tickets;

use App\Controller\BaseController;
use App\Entity\Response as EntityResponse;
use App\Entity\Ticket;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\ResponseFormType;
use App\Repository\ResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/%website_dashboard_path%/account/my-responses', name: 'dashboard_account_response_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountResponseController extends BaseController
{
    #[Route(path: '/{id}', name: 'index', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function index(
        Request $request,
        Ticket $ticket,
        ResponseRepository $responseRepository,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em
    ): Response {
        $response = new EntityResponse();
        $form = $this->createForm(ResponseFormType::class, $response)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $response->setTicket($ticket);
            $response->setUser($user);

            $em->persist($response);
            $em->flush();

            return $this->redirectToRoute('dashboard_account_response_index', ['id' => $ticket->getId()]);
        }

        $responses = $responseRepository->findBy(['ticket' => $ticket]);

        return $this->render('dashboard/shared/tickets/response.html.twig', compact('responses', 'ticket', 'form'));
    }
}
