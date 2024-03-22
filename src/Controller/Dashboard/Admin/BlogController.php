<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostFormType;
use App\Entity\Traits\HasRoles;
use App\Security\Voter\PostVoter;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/%website_dashboard_path%/main-panel/manage-blogs', name: 'dashboard_admin_blog_')]
#[IsGranted(HasRoles::TEAM)]
class BlogController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly PostRepository $postRepository
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    #[IsGranted(PostVoter::LIST)]
    public function index(Request $request, Security $security): Response
    {
        $page = $request->query->getInt('page', 1);
        $userId = $this->getUser()->getId();
        $canListAll = $security->isGranted(PostVoter::LIST_ALL);
        $rows = $this->postRepository->findForPagination($page, $canListAll ? null : $userId);

        return $this->render('dashboard/admin/blog/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted(PostVoter::CREATE)]
    public function new(Request $request, #[CurrentUser] User $user): Response
    {
        $post = new Post();
        $post->setAuthor($user);

        $form = $this->createForm(PostFormType::class, $post)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($post);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Content was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/blog/new.html.twig', compact('post', 'form'));
    }

    #[Route(path: '/{id}', name: 'show', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT])]
    public function show(Post $post): Response
    {
        $this->denyAccessUnlessGranted(PostVoter::SHOW, $post, $this->translator->trans("Content can only be shown to their authors."));
    
        return $this->render('dashboard/admin/blog/show.html.twig', compact('post'));
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    //#[IsGranted(PostVoter::MANAGE, subject: 'post', message: 'Content can only be edited by their authors.')]
    public function edit(Request $request, Post $post): Response
    {
        $this->denyAccessUnlessGranted(PostVoter::MANAGE, $post, $this->translator->trans("Content can only be edited by their authors."));

        $form = $this->createForm(PostFormType::class, $post)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

            return $this->redirectToRoute('dashboard_admin_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/blog/edit.html.twig', compact('post', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::POSITIVE_INT])]
    #[IsGranted(PostVoter::MANAGE, subject: 'post')]
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('post_deletion_'.$post->getId(), $request->request->get('_token'))) {
            $this->em->remove($post);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_blog_index', [], Response::HTTP_SEE_OTHER);
    }
}
