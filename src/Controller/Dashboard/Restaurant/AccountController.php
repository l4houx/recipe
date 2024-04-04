<?php

namespace App\Controller\Dashboard\Restaurant;

use App\Controller\BaseController;
use App\Entity\Traits\HasRoles;
use App\Form\RestaurantProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(HasRoles::RESTAURANT)]
class AccountController extends BaseController
{
    #[Route(path: '/%website_dashboard_path%/restaurant/profile', name: 'dashboard_restaurant_profile', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $form = $this->createForm(RestaurantProfileFormType::class, $this->getUser()->getRestaurant())->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($this->getUser()->getRestaurant());
                $em->flush();
                $this->addFlash('success', $translator->trans('Your restaurant profile has been successfully updated'));
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/restaurant/account/profile.html.twig', compact('form'));
    }
}
