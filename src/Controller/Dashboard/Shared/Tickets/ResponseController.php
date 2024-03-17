<?php

namespace App\Controller\Dashboard\Shared\Tickets;

use App\Entity\Response as EntityResponse;
use App\Entity\Ticket;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\ResponseFormType;
use App\Repository\ResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/%website_dashboard_path%/response', name: 'dashboard_response_')]
#[IsGranted(HasRoles::DEFAULT)]
class ResponseController extends AbstractController
{
    #[Route(path: '/{id}', name: 'index', methods: ['GET'])]
    public function index(
        Ticket $ticket,
        ResponseRepository $responseRepository,
        Request $request,
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

            return $this->redirectToRoute('dashboard_response_index', ['id' => $ticket->getId()]);
        }

        $responses = $responseRepository->findBy(['ticket' => $ticket]);

        return $this->render('dashboard/shared/tickets/response.html.twig', compact('responses', 'ticket', 'form'));
    }
}
