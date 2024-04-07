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

/** My Profile Creator */
#[Route(path: '/%website_dashboard_path%/creator', name: 'dashboard_creator_')]
#[IsGranted(HasRoles::CREATOR)]
class AccountController extends BaseController
{
    #[Route(path: '/account-profile', name: 'account_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(AccountCreatorFormType::class, $this->getUser())->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($this->getUser());
                $em->flush();
                $this->addFlash('info', $translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/account/creator/index.html.twig', compact('form'));
    }
}
