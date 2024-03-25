<?php

namespace App\Controller\Dashboard\Shared\Tickets;

use App\Controller\BaseController;
use App\Entity\Level;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\TicketFormType;
use App\Repository\ApplicationRepository;
use App\Repository\StatusRepository;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/account/my-tickets', name: 'dashboard_account_ticket_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountTicketController extends BaseController
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(
        #[CurrentUser] ?User $user,
        TicketRepository $ticketRepository,
        ApplicationRepository $applicationRepository
    ): Response {
        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $tickets = $ticketRepository->findAll();
        } else {
            $applications = $applicationRepository->findBy(['user' => $user->getId()]);

            $ticketsApp = $ticketRepository->findBy(['application' => $applications]);
            $ticketsUser = $ticketRepository->findBy(['user' => $user->getId()]);
            $tickets = array_merge($ticketsApp, $ticketsUser);
        }

        return $this->render('dashboard/shared/tickets/index.html.twig', compact('user', 'tickets'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        StatusRepository $statusRepository
    ): Response {
        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $statusRepository->findOneBy(['name' => $translator->trans('New')]);
            $ticket->setStatus($status);
            $ticket->setUser($user);

            $em->persist($ticket);
            $em->flush();

            return $this->redirectToRoute('dashboard_account_response_index', ['id' => $ticket->getId()]);
        }

        return $this->render('dashboard/shared/tickets/new.html.twig', compact('ticket', 'form'));
    }

    #[Route(path: '/status/{id}', name: 'status', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function status(
        Status $status,
        #[CurrentUser] ?User $user,
        TicketRepository $ticketRepository,
        ApplicationRepository $applicationRepository
    ): Response {
        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $tickets = $ticketRepository->findBy(['status' => $status]);
        } else {
            $applications = $applicationRepository->findBy(['user' => $user->getId()]);
            $ticketsApp = $ticketRepository->findBy(['application' => $applications, 'status' => $status]);
            $ticketsUser = $ticketRepository->findBy(['user' => $user->getId(), 'status' => $status]);
            $tickets = array_merge($ticketsApp, $ticketsUser);
        }

        return $this->render('dashboard/shared/tickets/index.html.twig', compact('tickets'));
    }

    #[Route(path: '/level/{id}', name: 'level', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function level(
        Level $level,
        #[CurrentUser] ?User $user,
        StatusRepository $statusRepository,
        TicketRepository $ticketRepository,
        ApplicationRepository $applicationRepository
    ): Response {
        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        $status = $statusRepository->findBy(['close' => true]);

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $criteriaAdmin = Criteria::create()
                ->andWhere(Criteria::expr()->notIn('status', $status))
                ->andWhere(Criteria::expr()->eq('level', $level))
            ;
            $tickets = $ticketRepository->matching($criteriaAdmin);
        } else {
            $applications = $applicationRepository->findBy(['user' => $user->getId()]);
            $criteriaUser = Criteria::create()
                ->andWhere(Criteria::expr()->in('application', $applications))
                ->orWhere(Criteria::expr()->eq('user', $user))
                ->andWhere(Criteria::expr()->notIn('status', $status))
                ->andWhere(Criteria::expr()->eq('level', $level))
            ;
            $tickets = $ticketRepository->matching($criteriaUser);
        }

        return $this->render('dashboard/shared/tickets/index.html.twig', compact('tickets'));
    }

    #[Route(path: '/close/{id}', name: 'close', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function close(
        Ticket $ticket,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        StatusRepository $statusRepository
    ): Response {
        $status = $statusRepository->findOneBy(['name' => $translator->trans('Clos')]);
        $ticket->setStatus($status);

        $em->flush();

        return $this->redirectToRoute('dashboard_main_account', [], Response::HTTP_SEE_OTHER);
    }
}
