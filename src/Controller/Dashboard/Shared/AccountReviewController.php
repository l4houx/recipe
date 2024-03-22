<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Review;
use App\Entity\Traits\HasRoles;
use App\Form\ReviewFormType;
use App\Repository\RecipeRepository;
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

#[Route(path: '/%website_dashboard_path%/account', name: 'dashboard_account_reviews_')]
//#[Route(path: '/%website_dashboard_path%/main-panel', name: 'dashboard_admin_reviews_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountReviewController extends BaseController
{
    public function __construct(
        private readonly RecipeRepository $recipeRepository,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/my-reviews', name: 'index', methods: ['GET'])]
    //#[Route(path: '/manage-reviews', name: 'index', methods: ['GET'])]
    public function index(Request $request, AuthorizationCheckerInterface $authChecker, PaginatorInterface $paginator): Response
    {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');
        $recipe = '' == $request->query->get('recipe') ? 'all' : $request->query->get('recipe');
        $visible = '' == $request->query->get('visible') ? 'all' : $request->query->get('visible');
        $rating = '' == $request->query->get('rating') ? 'all' : $request->query->get('rating');
        $slug = '' == $request->query->get('slug') ? 'all' : $request->query->get('slug');

        $user = 'all';
        if ($authChecker->isGranted(HasRoles::DEFAULT)) {
            $user = $this->getUser()->getSlug();
        }

        $rows = $paginator->paginate(
            $this->settingService->getReviews(['user' => $user, 'keyword' => $keyword, 'recipe' => $recipe, 'slug' => $slug, 'visible' => $visible, 'rating' => $rating])->getQuery(),
            $request->query->getInt('page', 1),
            10,
            ['wrap-queries' => true]
        );

        $user = $this->getUserOrThrow();

        return $this->render('dashboard/shared/review/index.html.twig', compact('rows', 'user'));
    }

    #[Route(path: '/my-reviews/{slug}/new', name: 'new', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UrlGeneratorInterface $url
    ): Response {
        $recipe = $this->recipeRepository->findOneBy([], ['id' => 'desc']);
        if (!$recipe) {
            $this->addFlash('danger', $translator->trans('The recipe not be found'));

            return $this->redirectToRoute('recipe_index', [], Response::HTTP_SEE_OTHER);
        }

        $review = new Review();

        $form = $this->createForm(ReviewFormType::class, $review)->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $review->setAuthor($this->getUser());
                $review->setRecipe($recipe);

                $em->persist($review);
                $em->flush();

                $this->addFlash('success', $translator->trans('Your review has been successfully saved'));

                return $this->redirect($url->generate(
                    'recipe_show', [
                        'slug' => $recipe->getSlug(),
                        'id' => $recipe->getId(),
                    ]
                ).'#reviews');
            } else {
                $this->addFlash('danger', $translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/shared/review/new.html.twig', compact('form', 'review', 'recipe'));
    }
}
