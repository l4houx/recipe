<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Level;
use App\Form\LevelFormType;
use App\Entity\Traits\HasRoles;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/%website_dashboard_path%/main-panel/manage-levels', name: 'dashboard_admin_level_')]
#[IsGranted(HasRoles::ADMIN)]
class LevelController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(LevelRepository $levelRepository): Response
    {
        $levels = $levelRepository->findAll();

        return $this->render('dashboard/admin/level/index.html.twig', compact('levels'));
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function add(
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        $level = new Level();
        $form = $this->createForm(LevelFormType::class, $level)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($level);
            $em->flush();

            $this->addFlash('success', $translator->trans('Level was created successfully.'));

            return $this->redirectToRoute('dashboard_admin_level_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/admin/level/new.html.twig', compact('level', 'form'));
    }
}
