<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RestaurantController extends BaseController
{
    #[Route(path: '/restaurant/{slug}', name: 'restaurant', methods: ['GET'])]
    public function restaurant(string $slug, EntityManagerInterface $em, SettingService $settingService, TranslatorInterface $translator): Response
    {
        /** @var User $user */
        $user = $settingService->getUsers(['restaurantslug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$user) {
            $this->addFlash('danger', $translator->trans('The restaurant not be found'));

            return $this->redirectToRoute('recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        $user->getRestaurant()->viewed();
        $em->persist($user->getRestaurant());
        $em->flush();

        return $this->render('restaurant/profile.html.twig', ['restaurant' => $user->getRestaurant()]
        );
    }
}
