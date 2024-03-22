<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Entity\Traits\HasRoles;
use App\Service\SecurityService;
use App\Controller\BaseController;
use App\Service\NotificationService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @method User getUser()
 */
#[IsGranted(HasRoles::DEFAULT)]
class NotificationsController extends BaseController
{
    #[Route(path: '/notifications/read', name: 'api_notification_read', methods: ['POST'])]
    public function readAll(SecurityService $securityService, NotificationService $service): JsonResponse
    {
        $user = $securityService->getUser();
        $service->readAll($user);

        return new JsonResponse();
    }
}
