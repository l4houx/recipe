<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Level;
use App\Form\LevelFormType;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/%website_dashboard_path%/admin/manage-levels', name: 'dashboard_admin_level_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class LevelController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly LevelRepository $levelRepository,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(LevelRepository $levelRepository): Response
    {
        $rows = $levelRepository->findAll();

        return $this->render('dashboard/admin/level/index.html.twig', compact('rows'));
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function newedit(Request $request, ?int $id = null): Response
    {
        if (!$id) {
            $level = new Level();
        } else {
            /** @var Level $level */
            $level = $this->settingService->getLevels(['id' => $id])->getQuery()->getOneOrNullResult();
            if (!$level) {
                $this->addFlash('danger', $this->translator->trans('The level can not be found'));

                return $this->redirectToRoute('dashboard_admin_level_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(LevelFormType::class, $level)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($level);
                $this->em->flush();
                if (!$id) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_level_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/level/new-edit.html.twig', compact('form', 'level'));
    }

    #[Route(path: '/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(Request $request, Level $level): Response
    {
        if ($this->isCsrfTokenValid('level_deletion_'.$level->getId(), $request->request->get('_token'))) {
            $this->em->remove($level);
            $this->em->flush();

            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        return $this->redirectToRoute('dashboard_admin_level_index', [], Response::HTTP_SEE_OTHER);
    }
}
