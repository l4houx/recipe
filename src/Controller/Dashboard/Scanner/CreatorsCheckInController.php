<?php

namespace App\Controller\Dashboard\Scanner;

use App\Controller\BaseController;
use App\Entity\OrderSubscription;
use App\Entity\RecipeDate;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%')]
class CreatorsCheckInController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/recipe-date/{reference}/creators-check-in', name: 'dashboard_scanner_recipe_date_creators_check_in', methods: ['GET'])]
    public function recipeDateCreators($reference, Request $request, PaginatorInterface $paginator): Response
    {
        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['reference' => $reference, 'restaurant' => $this->getUser()->getScanner()->getRestaurant()->getSlug()])->getQuery()->getOneOrNullResult();
        if (!$recipeDate) {
            $this->addFlash('danger', $this->translator->trans('The recipe date can not be found'));

            return $this->redirectToRoute('dashboard_main');
        }

        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $checkedin = '' == $request->query->get('checkedin') ? 'all' : $request->query->get('checkedin');

        $rows = $paginator->paginate($this->settingService->getOrderSubscriptions(['recipedate' => $reference, 'keyword' => $keyword, 'checkedin' => $checkedin])->getQuery(), $request->query->getInt('page', 1), 20, ['wrap-queries' => true]);

        return $this->render('dashboard/scanner/creatorsCheckIn/recipe-date-creators.html.twig', compact('recipeDate', 'rows'));
    }

    #[Route(path: '/recipe-date/{recipeDateReference}/creators-check-in/{subscriptionReference}/check-in', name: 'dashboard_scanner_subscription_check_in', methods: ['GET'])]
    public function subscriptionCheckIn(string $recipeDateReference, string $subscriptionReference)
    {
        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['reference' => $recipeDateReference, 'restaurant' => $this->getUser()->getScanner()->getRestaurant()->getSlug()])->getQuery()->getOneOrNullResult();
        if (!$recipeDate) {
            $this->addFlash('danger', $this->translator->trans('The recipe date can not be found'));

            return $this->redirectToRoute('dashboard_main');
        }

        /** @var OrderSubscription $subscription */
        $subscription = $this->settingService->getOrderSubscriptions(['keyword' => $subscriptionReference])->getQuery()->getOneOrNullResult();
        if ($subscription->getIsScanned()) {
            $this->addFlash('danger', $this->translator->trans('The subscription has already been scanned'));

            return $this->settingService->redirectToReferer('index');
        }

        $subscription->setIsScanned(true);

        $this->em->persist($subscription);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('The subscription has been successfully scanned'));

        return $this->settingService->redirectToReferer('index');
    }
}
