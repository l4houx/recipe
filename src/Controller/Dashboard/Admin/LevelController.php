<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Level;
use App\Entity\Traits\HasRoles;
use App\Form\LevelFormType;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/main-panel/manage-levels', name: 'dashboard_admin_level_')]
#[IsGranted(HasRoles::ADMIN)]
class LevelController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly LevelRepository $levelRepository,
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(LevelRepository $levelRepository): Response
    {
        $levels = $levelRepository->findAll();

        return $this->render('dashboard/admin/level/index.html.twig', compact('levels'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $level = new Level();
        $form = $this->createForm(LevelFormType::class, $level)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($level);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Level was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_level_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/level/new.html.twig', compact('level', 'form'));
    }

    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Request $request, Level $level): Response
    {
        $form = $this->createForm(LevelFormType::class, $level)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('info', $this->translator->trans('Level was edited successfully.'));

            return $this->redirectToRoute('dashboard_admin_level_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/level/edit.html.twig', compact('level', 'form'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Level $level): Response
    {
        if ($this->isCsrfTokenValid('level_deletion_'.$level->getId(), $request->request->get('_token'))) {
            $this->em->remove($level);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Level was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_level_index', [], Response::HTTP_SEE_OTHER);
    }
}
