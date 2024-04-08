<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\RecipeDate;
use App\Service\SettingService;
use App\Entity\OrderSubscription;
use App\Controller\BaseController;
use Symfony\Component\Asset\Packages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ScannerController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/login', name: 'api_scanner_login', methods: ['GET'])]
    public function login(Request $request, UserPasswordHasherInterface $userPasswordHasher, Packages $packages): Response
    {
        $username = $request->query->get('username');
        $password = $request->query->get('password');

        /** @var User $user */
        $user = $this->settingService->getUsers(['username' => $username, 'role' => 'scanner', 'isVerified' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$user) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('Invalid credentials')]);
        }

        if (!$userPasswordHasher->isPasswordValid($user, $password)) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('Invalid credentials')]);
        }

        if (!$user->isVerified()) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('The scanner account is disabled by the restaurant')]);
        }

        if (!$user->getApiKey()) {
            $user->setApiKey($this->settingService->generateReference(50));
        }

        $user->setLastLogin(new \DateTime());

        $this->em->persist($user);
        $this->em->flush();

        $baseUrl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
        if ($user->getScanner()->getRestaurant()->getLogoName()) {
            $restaurantLogo = $baseUrl.'/'.$packages->getUrl($user->getScanner()->getRestaurant()->getLogoPath());
        } else {
            $restaurantLogo = $user->getScanner()->getRestaurant()->getLogoPlaceholder();
        }

        /** @var Recipe $recipes */
        $recipes = $this->settingService->getRecipes(['canbescannedby' => $user->getScanner()])->getQuery()->getResult();
        $recipeDatesArray = [];
        $baseUrl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();

        /** @var Recipe $recipe */
        foreach ($recipes as $recipe) {
            foreach ($recipe->getRecipedates() as $recipeDate) {
                if ($recipeDate->canBeScannedBy($user->getScanner())) {
                    $recipeDateArray = [];
                    $recipeDateArray['recipeName'] = $recipe->getTitle();

                    $date = '';
                    if ($recipeDate->getStartdate()) {
                        $date = date($this->getParameter('date_format_simple'), $recipeDate->getStartdate()->getTimestamp());
                    }

                    $recipeDateArray['recipeDate'] = $date;
                    if ($recipeDate->getVenue()) {
                        $recipeDateArray['recipeVenue'] = $recipeDate->getVenue()->getName().': '.$recipeDate->getVenue()->stringifyAddress();
                    }
                    $recipeDateArray['recipeImage'] = $baseUrl.'/'.$packages->getUrl($recipe->getImagePath());
                    $recipeDateArray['recipeDateReference'] = $recipeDate->getReference();
                    $recipeDateArray['totalSales'] = $recipeDate->getOrderElementsQuantitySum();
                    $recipeDateArray['totalQuantity'] = $recipeDate->getSubscriptionsQuantitySum();
                    $recipeDateArray['totalCheckIns'] = $recipeDate->getScannedSubscriptionsCount();
                    $recipeDateArray['totalSalesPercentage'] = $recipeDate->getTotalSalesPercentage();
                    $recipeDateArray['totalCheckInPercentage'] = $recipeDate->getTotalCheckInPercentage();
                    array_push($recipeDatesArray, $recipeDateArray);
                }
            }
        }

        return $this->json([
            'type' => 'success',
            'apiKey' => $user->getApiKey(),
            'scannerName' => $user->getScanner()->getName(),
            'username' => $user->getUsername(),
            'restaurantName' => $user->getScanner()->getRestaurant()->getName(),
            'restaurantLogo' => $restaurantLogo,
            'showRecipeDateStats' => $user->getScanner()->getRestaurant()->getShowRecipeDateStatsOnScannerApp(),
            'allowTapToCheckIn' => $user->getScanner()->getRestaurant()->getAllowTapToCheckInOnScannerApp(),
            'recipeDatesArray' => $recipeDatesArray,
        ]);
    }

    #[Route(path: '/scanner/get-recipe-date-creators/{reference}', name: 'api_scanner_get_recipe_date_creators', methods: ['GET'])]
    public function getRecipeDateCreators(Request $request, string $reference): Response
    {
        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['restaurant' => $this->getUser()->getScanner()->getRestaurant()->getSlug(), 'reference' => $reference])->getQuery()->getOneOrNullResult();
        if (!$recipeDate) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('The recipe date can not be found')]);
        }

        $keyword = '' == $request->request->get('keyword') ? 'all' : $request->request->get('keyword');
        $checkedin = '' == $request->request->get('checkedin') ? 'all' : $request->request->get('checkedin');

        /** @var OrderSubscription $subscriptions */
        $subscriptions = $this->settingService->getOrderSubscriptions(['recipedate' => $reference, 'keyword' => $keyword, 'checkedin' => $checkedin])->getQuery()->getResult();

        $creatorsArray = [];

        /** @var OrderSubscription $subscription */
        foreach ($subscriptions as $subscription) {
            if ($subscription->getOrderelement()->getOrder()->getPayment()->getFirstname() && $subscription->getOrderelement()->getOrder()->getPayment()->getLastname()) {
                $creatorName = $subscription->getOrderelement()->getOrder()->getPayment()->getFirstname().' '.$subscription->getOrderelement()->getOrder()->getPayment()->getLastname();
            } else {
                $creatorName = $subscription->getOrderelement()->getOrder()->getUser()->getCrossRoleName();
            }

            $creatorArray['subscriptionReference'] = $subscription->getReference();
            $creatorArray['creatorName'] = $creatorName;
            $creatorArray['isSubscriptionScanned'] = $subscription->getIsScanned();
            array_push($creatorsArray, $creatorArray);
        }

        return $this->json($creatorsArray);
    }

    #[Route(path: '/scanner/recipe-date/{recipeDateReference}/grant-access/{subscriptionReference}', name: 'api_scanner_grant_access', methods: ['GET'])]
    public function grantAccess(string $recipeDateReference, string $subscriptionReference): Response
    {
        /** @var RecipeDate $recipeDate */
        $recipeDate = $this->settingService->getRecipeDates(['restaurant' => $this->getUser()->getScanner()->getRestaurant()->getSlug(), 'reference' => $recipeDateReference])->getQuery()->getOneOrNullResult();
        if (!$recipeDate) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('The recipe date can not be found')]);
        }
        /** @var OrderSubscription $subscription */
        $subscription = $this->settingService->getOrderSubscriptions(['reference' => $subscriptionReference])->getQuery()->getOneOrNullResult();

        if (!$subscription) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('The subscription can not be found')]);
        }

        if ($subscription->getOrderelement()->getRecipesubscription()->getRecipedate() != $recipeDate) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('The subscription is not valid for this recipe date')]);
        }

        if ($subscription->getIsScanned()) {
            return $this->json(['type' => 'danger', 'message' => $this->translator->trans('The subscription was already scanned at').' '.date($this->getParameter('date_format_simple'), $subscription->getUpdatedAt()->getTimestamp())]);
        }

        $subscription->setIsScanned(true);

        $this->em->persist($subscription);
        $this->em->flush();

        return $this->json(['type' => 'success', 'message' => $this->translator->trans('Access granted')]);
    }
}
