<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Revise;
use App\Entity\Content;
use App\Form\ReviseFormType;
use App\Service\ReviseService;
use App\Entity\Traits\HasRoles;
use App\Security\Voter\ReviseVoter;
use App\Repository\ReviseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @method User getUser()
 */
class ReviseController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/my-revises', name: 'dashboard_revise_index', methods: ['GET'])]
    #[IsGranted(HasRoles::DEFAULT)]
    public function revises(ReviseRepository $reviseRepository, PaginatorInterface $paginator): Response
    {
        $query = $reviseRepository->queryAllForUser($this->getUserOrThrow());
        $rows = $paginator->paginate($query->getQuery());

        return $this->render('dashboard/shared/content/revises.html.twig', compact('rows'));
    }

    #[Route(path: '/revise/{id<\d+>}/post-article', name: 'revise', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    #[IsGranted(ReviseVoter::ADD, subject: 'post')]
    public function revise(Request $request, TranslatorInterface $translator, Post $post, ReviseService $reviseService): Response
    {
        $revise = $reviseService->reviseFor($this->getUser(), $post);

        $form = $this->createForm(ReviseFormType::class, $revise)->handleRequest($request);
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->flashErrors($form);
            } else {
                $reviseService->submitRevise($revise);
                $this->addFlash(
                    'success',
                    $translator->trans('Your modification has been saved, you can revert your changes as long as they have not been validated')
                );
            }
        }

        return $this->render('content/revise.html.twig', compact('revise', 'form'));
    }

    #[Route(path: '/revise/{id<\d+>}/post-article', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    #[IsGranted(ReviseVoter::DELETE, subject: 'revise')]
    public function delete(Request $request, Revise $revise, TranslatorInterface $translator, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('revise_deletion_'.$revise->getId(), $request->request->get('_token'))) {
            $em->remove($revise);
            $em->flush();

            $this->addFlash('danger', $translator->trans('Content was deleted successfully.'));
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
