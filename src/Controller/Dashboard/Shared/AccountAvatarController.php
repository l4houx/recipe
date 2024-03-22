<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\DTO\AccountUpdatedAvatarDTO;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Service\AccountUpdatedService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Handles user avatar change.
 */
#[IsGranted(HasRoles::DEFAULT)]
class AccountAvatarController extends BaseController
{
    #[Route('/%website_dashboard_path%/account/avatar', name: 'dashboard_account_avatar', methods: ['POST'])]
    public function accountAvatar(
        Request $request,
        ValidatorInterface $validator,
        AccountUpdatedService $accountUpdatedService,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $data = new AccountUpdatedAvatarDTO($request->files->get('avatar'), $user);
        $errors = $validator->validate($data);

        if ($errors->count() > 0) {
            $this->addFlash('danger', (string) $errors->get(0)->getMessage());
        } else {
            $accountUpdatedService->updatedAvatar($data);
            $em->flush();
            $this->addFlash('success', $translator->trans('Avatar updated successfully.'));
        }

        return $this->redirectToRoute('dashboard_account_edit', [], Response::HTTP_SEE_OTHER);
    }
}
