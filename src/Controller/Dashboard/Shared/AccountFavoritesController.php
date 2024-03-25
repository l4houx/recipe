<?php

namespace App\Controller\Dashboard\Shared;

use App\Controller\BaseController;
use App\Entity\Recipe;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/account/my-favorites', name: 'dashboard_account_favorites_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountFavoritesController extends BaseController
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUserOrThrow();

        $rows = $paginator->paginate(
            $this->settingService->getRecipes(['addedtofavoritesby' => $this->getUser()])->getQuery(),
            $request->query->getInt('page', 1),
            12,
            ['wrap-queries' => true]
        );

        return $this->render('dashboard/shared/recipe/favorites.html.twig', compact('user', 'rows'));
    }

    #[Route(path: '/create/{slug}', name: 'create', methods: ['GET', 'POST'])]
    #[Route(path: '/remove/{slug}', name: 'remove', methods: ['POST'])]
    public function createRemove(string $slug, EntityManagerInterface $em, TranslatorInterface $translator): JsonResponse
    {
        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$recipe) {
            return new JsonResponse(['danger' => $translator->trans('The recipe can not be found')]);
        }

        if ($recipe->isAddedToFavoritesBy($this->getUser())) {
            $this->getUser()->removeFavorite($recipe);

            $em->persist($this->getUser());
            $em->flush();

            return new JsonResponse(['danger' => $translator->trans('The recipe has been removed from your favorites')]);
        } else {
            $this->getUser()->addFavorite($recipe);

            $em->persist($this->getUser());
            $em->flush();

            return new JsonResponse(['success' => $translator->trans('The recipe has been added to your favorites')]);
        }
    }
}
