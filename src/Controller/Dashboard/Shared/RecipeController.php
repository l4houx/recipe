<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Recipe;
use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Form\RecipeFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/')]
#[IsGranted(HasRoles::DEFAULT)]
class RecipeController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService,
        private readonly AuthorizationCheckerInterface $authChecker
    ) {
    }

    #[Route(path: '/admin/manage-recipes', name: 'dashboard_admin_recipe_index', methods: ['GET'])]
    #[Route(path: '/restaurant/my-recipes', name: 'dashboard_restaurant_recipe_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUserOrThrow();

        $slug = '' == $request->query->get('slug') ? 'all' : $request->query->get('slug');
        $category = '' == $request->query->get('category') ? 'all' : $request->query->get('category');
        $venue = '' == $request->query->get('venue') ? 'all' : $request->query->get('venue');
        $elapsed = '' == $request->query->get('elapsed') ? 'all' : $request->query->get('elapsed');
        $isOnline = '' == $request->query->get('isOnline') ? 'all' : $request->query->get('isOnline');

        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        $rows = $paginator->paginate($this->settingService->getRecipes(['slug' => $slug, 'category' => $category, 'venue' => $venue, 'elapsed' => $elapsed, 'isOnline' => $isOnline, 'restaurant' => $restaurant, 'sort' => 'startdate', 'restaurantEnabled' => 'all', 'sort' => 'r.createdAt', 'order' => 'DESC'])->getQuery(), $request->query->getInt('page', 1), HasLimit::RECIPE_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/shared/recipe/index.html.twig', compact('user', 'rows'));
    }

    #[Route(path: '/restaurant/my-recipes/new', name: 'dashboard_restaurant_recipe_new', methods: ['GET', 'POST'])]
    #[Route(path: '/restaurant/my-recipes/{slug}/edit', name: 'dashboard_restaurant_recipe_edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        if (!$slug) {
            $recipe = new Recipe();
            $form = $this->createForm(RecipeFormType::class, $recipe, ['validation_groups' => ['create', 'Default']])->handleRequest($request);
        } else {
            /** @var Recipe $recipe */
            $recipe = $this->settingService->getRecipes(['published' => 'all', 'elapsed' => 'all', 'slug' => $slug, 'restaurant' => $restaurant, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();
            if (!$recipe) {
                $this->addFlash('danger', $this->translator->trans('The recipe can not be found'));

                return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
            }

            $form = $this->createForm(RecipeFormType::class, $recipe, ['validation_groups' => ['update', 'Default']])->handleRequest($request);
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                foreach ($recipe->getImages() as $image) {
                    $image->setRecipe($recipe);
                }

                foreach ($recipe->getRecipedates() as $recipedate) {
                    $recipedate->setRecipe($recipe);

                    if (!$slug || !$recipedate->getReference()) {
                        $recipedate->setReference($this->settingService->generateReference(10));
                    }

                    if ($recipedate->getSeatingPlan()) {
                        $recipedate->setVenue($recipedate->getSeatingPlan()->getVenue());
                    }

                    foreach ($recipedate->getSubscriptions() as $recipesubscription) {
                        $recipesubscription->setRecipedate($recipedate);
                        if (!$slug || !$recipesubscription->getReference()) {
                            $recipesubscription->setReference($this->settingService->generateReference(10));
                        }
                    }
                }

                if (!$slug) {
                    $recipe->setRestaurant($this->getUser()->getRestaurant());
                    $recipe->setReference($this->settingService->generateReference(10));
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                $this->em->persist($recipe);
                $this->em->flush();

                if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
                    return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
                } elseif ($this->authChecker->isGranted(HasRoles::APPLICATION)) {
                    return $this->redirectToRoute('dashboard_admin_recipe_index', [], Response::HTTP_SEE_OTHER);
                }
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        $seatingPlansSectionsSeatsCountArray = [];
        foreach ($this->settingService->getVenuesSeatingPlans(['restaurant' => $this->getUser()->getRestaurant()->getSlug()])->getQuery()->getResult() as $seatingPlan) {
            $seatingPlansSectionsSeatsCountArray[$seatingPlan->getId()] = $seatingPlan->getSectionsSeatsQuantityArray();
        }

        return $this->render('dashboard/shared/recipes/new-edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form,
            'seatingPlansSectionsSeatsCountArrayJson' => json_encode($seatingPlansSectionsSeatsCountArray),
        ]);
    }

    #[Route(path: '/admin/manage-recipes/{slug}/delete-permanently', name: 'dashboard_admin_recipe_delete_permanently', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/admin/manage-recipes/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-recipes/{slug}/delete-permanently', name: 'dashboard_restaurant_recipe_delete_permanently', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-recipes/{slug}/delete', name: 'dashboard_restaurant_recipe_delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug, 'isOnline' => 'all', 'elapsed' => 'all', 'restaurant' => $restaurant, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$recipe) {
            $this->addFlash('danger', $this->translator->trans('The recipe can not be found'));

            return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($recipe->getOrderElementsQuantitySum() > 0) {
            $this->addFlash('danger', $this->translator->trans('The recipe can not be deleted because it has one or more orders'));

            return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $recipe->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted permanently successfully.'));
        } else {
            $this->addFlash('info', $this->translator->trans('Content was deleted successfully.'));
        }

        $this->em->remove($recipe);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/admin/manage-recipes/{slug}/restore', name: 'dashboard_admin_recipe_restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug, 'isOnline' => 'all', 'elapsed' => 'all', 'restaurant' => 'all', 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$recipe) {
            $this->addFlash('danger', $this->translator->trans('The recipe can not be found'));

            return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        $recipe->setDeletedAt(null);

        $this->em->persist($recipe);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/restaurant/my-recipes/{slug}/publish', name: 'dashboard_restaurant_recipe_publish', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-recipes/{slug}/draft', name: 'dashboard_restaurant_recipe_draft', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug, 'isOnline' => 'all', 'elapsed' => 'all', 'restaurant' => $restaurant, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();

        if (!$recipe) {
            $this->addFlash('danger', $this->translator->trans('The recipe can not be found'));

            return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        if (true === $recipe->getIsOnline()) {
            $recipe->setIsOnline(false);
            $this->addFlash('info', $this->translator->trans('The recipe has been unpublished and will not be included in the search results'));
        } else {
            $recipe->setIsOnline(true);
            $this->addFlash('success', $this->translator->trans('The recipe has been published and will figure in the search results'));
        }

        $this->em->persist($recipe);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_restaurant_recipe_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/admin/manage-recipes/{slug}/details', name: 'dashboard_admin_recipe_details', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/restaurant/my-recipes/{slug}/details', name: 'dashboard_restaurant_recipe_details', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function details(string $slug): Response
    {
        $restaurant = 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug, 'isOnline' => 'all', 'elapsed' => 'all', 'restaurant' => $restaurant, 'restaurantEnabled' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$recipe) {
            return new Response($this->translator->trans('The recipe can not be found'));
        }

        return $this->render('dashboard/shared/recipes/details.html.twig', compact('user', 'recipe'));
    }
}
