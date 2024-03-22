<?php

namespace App\Controller;

use App\Service\SettingService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReviewController extends BaseController
{
    #[Route(path: '/recipe/{slug}/reviews', name: 'reviews', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function review(
        Request $request,
        PaginatorInterface $paginator,
        TranslatorInterface $translator,
        SettingService $settingService,
        string $slug
    ): Response {
        $keyword = '' == $request->query->get('keyword') ? 'all' : $request->query->get('keyword');

        $recipe = $settingService->getRecipes(['slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$recipe) {
            $this->addFlash('danger', $translator->trans('The recipe not be found'));

            $referer = $request->headers->get('referer');

            return $this->redirect($referer); // return to previous page
        }

        $reviews = $paginator->paginate(
            $settingService->getReviews(['recipe' => $recipe->getSlug(), 'keyword' => $keyword])->getQuery(),
            $request->query->getInt('page', 1),
            10,
            ['wrap-queries' => true]
        );

        return $this->render('recipe/review.html.twig', compact('recipe', 'reviews'));
    }
}
