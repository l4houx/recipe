<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @method User getUser()
 */
#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class NotificationsController extends BaseController
{
    #[Route(path: '/creator/my-notifications', name: 'dashboard_creator_notification_index', methods: ['GET'])]
    public function index(NotificationService $notificationService): Response
    {
        $rows = $notificationService->forUser($this->getUser());

        return $this->render('dashboard/shared/notifications/index.html.twig', compact('rows'));
    }
}
