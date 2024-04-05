<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Recipe;
use App\Entity\Review;
use App\Entity\Traits\HasRoles;
use App\Form\ReviewFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%')]
#[IsGranted(HasRoles::DEFAULT)]
class ReviewController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/admin/manage-reviews', name: 'dashboard_admin_review_index', methods: ['GET'])]
    #[Route(path: '/restaurant/my-reviews', name: 'dashboard_restaurant_review_index', methods: ['GET'])]
    #[Route(path: '/creator/my-reviews', name: 'dashboard_creator_review_index', methods: ['GET'])]
    public function index(Request $request, AuthorizationCheckerInterface $authChecker, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $recipe = '' == $request->query->get('recipe') ? 'all' : $request->query->get('recipe');
        $isVisible = '' == $request->query->get('isVisible') ? 'all' : $request->query->get('isVisible');
        $rating = '' == $request->query->get('rating') ? 'all' : $request->query->get('rating');
        $slug = '' == $request->query->get('slug') ? 'all' : $request->query->get('slug');

        $user = 'all';
        if ($authChecker->isGranted(HasRoles::CREATOR)) {
            $user = $this->getUser()->getSlug();
        }

        $restaurant = 'all';
        if ($authChecker->isGranted(HasRoles::RESTAURANT)) {
            $restaurant = $this->getUser()->getRestaurant()->getSlug();
        }

        $rows = $paginator->paginate(
            $this->settingService->getReviews(['user' => $user, 'restaurant' => $restaurant, 'keyword' => $keyword, 'recipe' => $recipe, 'slug' => $slug, 'isVisible' => $isVisible, 'rating' => $rating])->getQuery(),
            $request->query->getInt('page', 1),
            10,
            ['wrap-queries' => true]
        );

        $user = $this->getUserOrThrow();

        return $this->render('dashboard/shared/review/index.html.twig', compact('rows', 'user'));
    }

    #[Route(path: '/creator/my-reviews/{slug}/new', name: 'dashboard_creator_review_new', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function new(Request $request, string $slug, UrlGeneratorInterface $url): Response
    {
        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug, 'elapsed' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$recipe) {
            $this->addFlash('danger', $this->translator->trans('The recipe not be found'));

            return $this->redirectToRoute('recipes', [], Response::HTTP_SEE_OTHER);
        }

        $review = new Review();

        $form = $this->createForm(ReviewFormType::class, $review)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $review->setAuthor($this->getUser());
                $review->setRecipe($recipe);

                $this->em->persist($review);
                $this->em->flush();

                $this->addFlash('success', $this->translator->trans('Your review has been successfully saved'));

                return $this->redirect($url->generate('recipe', ['slug' => $recipe->getSlug()]).'#reviews');
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/review/new.html.twig', compact('form', 'review', 'recipe'));
    }

    #[Route(path: '/admin/manage-reviews/{slug}/show', name: 'dashboard_admin_review_show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/admin/manage-reviews/{slug}/hide', name: 'dashboard_admin_review_hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var Review $review */
        $review = $this->settingService->getReviews(['slug' => $slug, 'isVisible' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$review) {
            $this->addFlash('danger', $this->translator->trans('The review can not be found'));

            return $this->redirectToRoute('dashboard_admin_review_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($review->getIsVisible()) {
            $review->setIsVisible(false);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $review->setIsVisible(true);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($review);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_review_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/admin/manage-reviews/{slug}/delete-permanently', name: 'dashboard_admin_review_delete_permanently', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/admin/manage-reviews/{slug}/delete', name: 'dashboard_admin_review_delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var Review $review */
        $review = $this->settingService->getReviews(['slug' => $slug, 'isVisible' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$review) {
            $this->addFlash('danger', $this->translator->trans('The review can not be found'));

            return $this->redirectToRoute('dashboard_admin_review_index', [], Response::HTTP_SEE_OTHER);
        }

        if (null !== $review->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted permanently successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        }

        $review->setIsVisible(false);

        $this->em->persist($review);
        $this->em->flush();
        $this->em->remove($review);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_review_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/admin/manage-reviews/{slug}/restore', name: 'dashboard_admin_review_restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var Review $review */
        $review = $this->settingService->getReviews(['slug' => $slug, 'isVisible' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$review) {
            $this->addFlash('danger', $this->translator->trans('The review can not be found'));

            return $this->redirectToRoute('dashboard_admin_review_index', [], Response::HTTP_SEE_OTHER);
        }

        $review->setDeletedAt(null);

        $this->em->persist($review);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_review_index', [], Response::HTTP_SEE_OTHER);
    }
}
