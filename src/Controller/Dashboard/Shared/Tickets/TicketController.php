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

#[IsGranted(HasRoles::DEFAULT)]
#[Route(path: '/%website_dashboard_path%/my-tickets', name: 'dashboard_ticket_')]
class TicketController extends BaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly StatusRepository $statusRepository,
        private readonly TicketRepository $ticketRepository,
        private readonly ApplicationRepository $applicationRepository,
        private readonly EntityManagerInterface $em,
        private readonly Security $security
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $rows = $this->ticketRepository->findAll();
        } else {
            $applications = $this->applicationRepository->findBy(['user' => $user->getId()]);

            $ticketsApp = $this->ticketRepository->findBy(['application' => $applications]);
            $ticketsUser = $this->ticketRepository->findBy(['user' => $user->getId()]);
            $rows = array_merge($ticketsApp, $ticketsUser);
        }

        return $this->render('dashboard/shared/tickets/index.html.twig', compact('user', 'rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, #[CurrentUser] ?User $user): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $status = $this->statusRepository->findOneBy(['name' => 'New']);
                $ticket->setStatus($status);
                $ticket->setUser($user);

                $this->em->persist($ticket);
                $this->em->flush();

                $this->addFlash('success', $this->translator->trans('Ticket was created successfully.'));

                return $this->redirectToRoute('dashboard_response_index', ['id' => $ticket->getId()]);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/tickets/new.html.twig', compact('ticket', 'form'));
    }

    #[Route(path: '/status/{id}', name: 'status', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function status(Status $status, #[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $tickets = $this->ticketRepository->findBy(['status' => $status]);
        } else {
            $applications = $this->applicationRepository->findBy(['user' => $user->getId()]);
            $ticketsApp = $this->ticketRepository->findBy(['application' => $applications, 'status' => $status]);
            $ticketsUser = $this->ticketRepository->findBy(['user' => $user->getId(), 'status' => $status]);
            $tickets = array_merge($ticketsApp, $ticketsUser);
        }

        return $this->render('dashboard/shared/tickets/index.html.twig', compact('tickets'));
    }

    #[Route(path: '/level/{id}', name: 'level', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function level(Level $level, #[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }

        $status = $this->statusRepository->findBy(['isClose' => true]);

        if ($this->security->isGranted(HasRoles::ADMIN)) {
            $criteriaAdmin = Criteria::create()
                ->andWhere(Criteria::expr()->notIn('status', $status))
                ->andWhere(Criteria::expr()->eq('level', $level))
            ;
            $tickets = $this->ticketRepository->matching($criteriaAdmin);
        } else {
            $applications = $this->applicationRepository->findBy(['user' => $user->getId()]);
            $criteriaUser = Criteria::create()
                ->andWhere(Criteria::expr()->in('application', $applications))
                ->orWhere(Criteria::expr()->eq('user', $user))
                ->andWhere(Criteria::expr()->notIn('status', $status))
                ->andWhere(Criteria::expr()->eq('level', $level))
            ;
            $tickets = $this->ticketRepository->matching($criteriaUser);
        }

        return $this->render('dashboard/shared/tickets/index.html.twig', compact('tickets'));
    }

    #[Route(path: '/close/{id}', name: 'close', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function close(Ticket $ticket): Response
    {
        $status = $this->statusRepository->findOneBy(['name' => 'Closed']);
        $ticket->setStatus($status);

        $this->em->flush();

        return $this->redirectToRoute('dashboard_creator_account_dashboard', [], Response::HTTP_SEE_OTHER);
    }
}
