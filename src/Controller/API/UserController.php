<?php

namespace App\Controller\API;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

// Used in orders page
class UserController extends BaseController
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/get-restaurants', name: 'get_restaurants', methods: ['GET'])]
    #[Route(path: '/get-users', name: 'get_users', methods: ['GET'])]
    public function getUsers(Request $request): Response
    {
        if (!$this->isGranted(HasRoles::ADMINAPPLICATION) && !$this->isGranted(HasRoles::RESTAURANT)) {
            throw new AccessDeniedHttpException();
        }

        $q = '' == $request->query->get('q') ? 'all' : $request->query->get('q');
        $limit = '' == $request->query->get('limit') ? 10 : $request->query->get('limit');

        if ('get_restaurants' == $request->get('_route')) {
            /** @var User $users */
            $users = $this->settingService->getUsers(['role' => 'restaurant', 'restaurantname' => $q, 'limit' => $limit])->getQuery()->getResult();
        } elseif ('get_users' == $request->get('_route')) {
            if ($this->isGranted(HasRoles::RESTAURANT)) {
                $creators = $this->settingService->getUsers(['keyword' => $q, 'role' => 'creator', 'hasboughtsubscriptionforrestaurant' => $this->getUser()->getRestaurant()->getSlug(), 'limit' => $limit])->getQuery()->getResult();
                $pointsofsale = $this->settingService->getUsers(['keyword' => $q, 'role' => 'pointofsale', 'limit' => $limit])->getQuery()->getResult();
                $users = array_merge($creators, $pointsofsale);
            } else {
                $creators = $this->settingService->getUsers(['keyword' => $q, 'role' => 'creator', 'limit' => $limit])->getQuery()->getResult();
                $pointsofsale = $this->settingService->getUsers(['keyword' => $q, 'role' => 'point_of_sale', 'limit' => $limit])->getQuery()->getResult();
                $users = array_merge($creators, $pointsofsale);
            }
        }

        $results = [];

        /** @var User $user */
        foreach ($users as $user) {
            if ('get_restaurants' == $request->get('_route')) {
                $result = ['id' => $user->getRestaurant()->getSlug(), 'text' => $user->getRestaurant()->getName()];
            } elseif ('get_users' == $request->get('_route')) {
                $result = ['id' => $user->getSlug(), 'text' => $user->getCrossRoleName()];
            }
            array_push($results, $result);
        }

        return $this->json($results);
    }

    #[Route(path: '/get-restaurant/{slug}', name: 'get_restaurant', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/get-user/{slug}', name: 'get_user', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function getUserEntity(Request $request, ?string $slug = null): Response
    {
        if ('get_restautant' == $request->get('_route')) {
            if (!$this->isGranted(HasRoles::ADMINAPPLICATION)) {
                throw new AccessDeniedHttpException();
            }

            /** @var User $user */
            $user = $this->settingService->getUsers(['role' => 'restaurant', 'restaurantslug' => $slug])->getQuery()->getOneOrNullResult();

            return $this->json(['slug' => $user->getRestaurant()->getSlug(), 'text' => $user->getRestaurant()->getName()]);
        } elseif ('get_user' == $request->get('_route')) {
            if (!$this->isGranted(HasRoles::ADMINAPPLICATION) && !$this->isGranted(HasRoles::RESTAURANT)) {
                throw new AccessDeniedHttpException();
            }

            $hasboughtsubscriptionforrestaurant = 'all';
            if ($this->isGranted(HasRoles::RESTAURANT)) {
                $hasboughtsubscriptionforrestaurant = $this->getUser()->getRestaurant()->getSlug();
            }

            /** @var User $user */
            $user = $this->settingService->getUsers(['role' => 'creator', 'slug' => $slug, 'hasboughtsubscriptionforrestaurant' => $hasboughtsubscriptionforrestaurant])->getQuery()->getOneOrNullResult();

            if (!$user) {
                /** @var User $user */
                $user = $this->settingService->getUsers(['role' => 'point_of_sale', 'slug' => $slug, 'createdbyrestaurantslug' => $this->getUser()->getRestaurant()->getSlug()])->getQuery()->getOneOrNullResult();
            }

            return $this->json(['slug' => $user->getSlug(), 'text' => $user->getFullName()]);
        }
    }
}
