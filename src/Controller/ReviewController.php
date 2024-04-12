<?php

namespace App\Controller;

use App\Entity\Traits\HasLimit;
use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReviewController extends BaseController
{
    #[Route(path: '/recipe/{slug}/reviews', name: 'recipe_reviews', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function review(
        Request $request,
        PaginatorInterface $paginator,
        TranslatorInterface $translator,
        SettingService $settingService,
        string $slug
    ): Response {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');

        $recipe = $settingService->getRecipes(['slug' => $slug, 'elapsed' => 'all'])->getQuery()->getOneOrNullResult();
        if (!$recipe) {
            $this->addFlash('danger', $translator->trans('The recipe not be found'));

            return $this->redirectToRoute('recipes', [], Response::HTTP_SEE_OTHER);
        }

        $rows = $paginator->paginate(
            $settingService->getReviews(['recipe' => $recipe->getSlug(), 'keyword' => $keyword])->getQuery(),
            $request->query->getInt('page', 1),
            $settingService->getSettings('reviews_per_page'),
            ['wrap-queries' => true]
        );

        return $this->render('recipe/review.html.twig', compact('recipe', 'rows'));
    }
}
