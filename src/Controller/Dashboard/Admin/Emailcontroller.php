<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use App\Infrastructural\Mail\Mail;
use App\Repository\RecipeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/%website_dashboard_path%/admin/manage-emails', name: 'dashboard_admin_email_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class Emailcontroller extends AdminBaseController
{
    #[Route(path: '/email', name: 'preview')]
    public function index(): Response
    {
        return $this->render('dashboard/admin/pages/email.html.twig');
    }

    #[Route(path: '/email/{format}', name: 'mail')]
    public function mail(
        string $format,
        Mail $mail,
        RecipeRepository $recipeRepository
    ): Response {
        $recipe = $recipeRepository->findOneBy(['author' => $this->getUser()]);

        $email = $mail->sendEmail('mail/recipe/new_recipe.html.twig', [
            'author' => $this->getUser(),
            'is_recipe_owner' => true,
            'recipe' => $recipe,
        ]);

        if ('html' === $format) {
            return new Response((string) $email->getHtmlBody());
        }

        return new Response("<pre>{$email->getTextBody()}</pre>");
    }
}
