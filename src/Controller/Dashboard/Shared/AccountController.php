<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Form\AccountCreatorFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/** My Profile Creator (User) */
#[Route(path: '/%website_dashboard_path%/creator', name: 'dashboard_creator_')]
#[IsGranted(HasRoles::CREATOR)]
class AccountController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/account-profile', name: 'account_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $user = $this->getUserOrThrow();
        $form = $this->createForm(AccountCreatorFormType::class, $this->getUser())->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($this->getUser());
                $this->em->flush();
                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/account/creator/index.html.twig', compact('form', 'user'));
    }
}
