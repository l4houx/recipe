<?php

namespace App\Controller;

use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @method User getUser()
 */
#[IsGranted(HasRoles::DEFAULT)]
class NotificationsController extends BaseController
{
    #[Route(path: '/notifications', name: 'notifications')]
    public function index(NotificationService $service): Response
    {
        $notifications = $service->forUser($this->getUser());

        return $this->render('notifications/index.html.twig', compact('notifications'));
    }
}
