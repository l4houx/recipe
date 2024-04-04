<?php

namespace App\Controller\Dashboard\Creator;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/creator/my-followings', name: 'dashboard_creator_following_')]
#[IsGranted(HasRoles::DEFAULT)]
class FollowingController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $rows = $paginator->paginate($this->settingService->getUsers(['followedby' => $this->getUser()])->getQuery(), $request->query->getInt('page', 1), 12, ['wrap-queries' => true]);

        return $this->render('dashboard/creator/following.html.twig', compact('rows'));
    }

    #[Route(path: '/new/{slug}', name: 'new', methods: ['GET', 'POST'], condition: 'request.isXmlHttpRequest()', requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/remove/{slug}', name: 'remove', methods: ['POST'], condition: 'request.isXmlHttpRequest()', requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newRemove(string $slug): JsonResponse
    {
        /** @var User $user */
        $user = $this->settingService->getUsers(['restaurantslug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$user) {
            return new JsonResponse(['danger' => $this->translator->trans('The restaurant can not be found')]);
        }

        if ($user->getRestaurant()->isFollowedBy($this->getUser())) {
            $user->getRestaurant()->removeFollowedby($this->getUser());
            $this->em->persist($user->getRestaurant());
            $this->em->flush();

            return new JsonResponse(['success' => $this->translator->trans('You are no longer following this restaurant')]);
        } else {
            $user->getRestaurant()->addFollowedby($this->getUser());
            $this->em->persist($user->getRestaurant());
            $this->em->flush();

            return new JsonResponse(['success' => $this->translator->trans('You are following this restaurant')]);
        }
    }
}
