<?php

namespace App\Controller\Dashboard\Creator;

use App\Controller\BaseController;
use App\Entity\Recipe;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/%website_dashboard_path%/creator/my-favorites', name: 'dashboard_creator_favorites_')]
#[IsGranted(HasRoles::DEFAULT)]
class FavoritesController extends BaseController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
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

        return $this->render('dashboard/creator/favorites.html.twig', compact('user', 'rows'));
    }

    #[Route(path: '/new/{slug}', name: 'new', methods: ['GET'], condition: 'request.isXmlHttpRequest()', requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/remove/{slug}', name: 'remove', methods: ['POST'], condition: 'request.isXmlHttpRequest()', requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newRemove(string $slug): JsonResponse
    {
        /** @var Recipe $recipe */
        $recipe = $this->settingService->getRecipes(['slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$recipe) {
            return new JsonResponse(['danger' => $this->translator->trans('The recipe can not be found')]);
        }

        if ($recipe->isAddedToFavoritesBy($this->getUser())) {
            $this->getUser()->removeFavorite($recipe);

            $this->em->persist($this->getUser());
            $this->em->flush();

            return new JsonResponse(['danger' => $this->translator->trans('The recipe has been removed from your favorites')]);
        } else {
            $this->getUser()->addFavorite($recipe);

            $this->em->persist($this->getUser());
            $this->em->flush();

            return new JsonResponse(['success' => $this->translator->trans('The recipe has been added to your favorites')]);
        }
    }
}
