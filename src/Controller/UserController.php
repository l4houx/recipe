<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route(path: '/profile/@{slug}', name: 'user_profil', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function userProfil(
        User $user,
        CommentRepository $commentRepository,
        ReviewRepository $reviewRepository
    ): Response {
        $lastComments = $commentRepository->findLastByUser($user, 4);
        $lastReviews = $reviewRepository->findLastByUser($user, 4);

        return $this->render('user/profile-manager.html.twig', compact('user', 'lastComments', 'lastReviews'));
    }
}
