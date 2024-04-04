<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasLimit;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use App\Entity\HelpCenterArticle;
use App\Form\HelpCenterArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\HelpCenterArticleRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/%website_dashboard_path%/admin/manage-help-center', name: 'dashboard_admin_help_center_article_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class HelpCenterArticleController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingService
    ) {
    }

    #[Route(path: '/articles', name: 'index', methods: ['GET'])]
    /*
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $keyword = '' === $request->query->get('keyword') ? 'all' : $request->query->get('keyword');

        $articles = $paginator->paginate($this->settingService->getHelpCenterArticles(['keyword' => $keyword, 'isHidden' => 'all', 'sort' => 'updatedAt', 'order' => 'DESC']), $request->query->getInt('page', 1), 10, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/helpCenter/articles/index.html.twig', compact('articles'));
    }
    */

    public function index(Request $request, PaginatorInterface $paginator, HelpCenterArticleRepository $helpCenterArticleRepository): Response
    {
        $query = $helpCenterArticleRepository->findBy(['isOnline' => true], ['updatedAt' => 'DESC']);
        $page = $request->query->getInt('page', 1);

        $rows = $paginator->paginate($query, $page, HasLimit::HELPCENTERARTICLE_LIMIT, ['wrap-queries' => true]);

        return $this->render('dashboard/admin/helpCenter/articles/index.html.twig', compact('rows'));
    }

    #[Route(path: '/articles/new', name: 'new', methods: ['GET', 'POST'])]
    #[Route(path: '/articles/{slug}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function newedit(Request $request, ?string $slug = null): Response
    {
        if (!$slug) {
            $article = new HelpCenterArticle();
        } else {
            /** @var HelpCenterArticle $article */
            $article = $this->settingService->getHelpCenterArticles(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
            if (!$article) {
                $this->addFlash('danger', $this->translator->trans('The article can not be found'));

                return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $form = $this->createForm(HelpCenterArticleType::class, $article)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->em->persist($article);
                $this->em->flush();
                if (!$slug) {
                    $this->addFlash('success', $this->translator->trans('Content was created successfully.'));
                } else {
                    $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
                }

                return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        }

        return $this->render('dashboard/admin/helpCenter/articles/new-edit.html.twig', compact('form', 'article'));
    }

    #[Route(path: '/articles/{slug}/featured', name: 'featured', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/articles/{slug}/notfeatured', name: 'notfeatured', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function featured(string $slug): Response
    {
        /** @var HelpCenterArticle $article */
        $article = $this->settingService->getHelpCenterArticles(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$article) {
            $this->addFlash('danger', $this->translator->trans('The article can not be found'));

            return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
        }
        if (true === $article->getIsFeatured()) {
            $article->setIsFeatured(false);
            $this->addFlash('danger', $this->translator->trans('Content is not featured anymore.'));
        } else {
            $article->setIsFeatured(true);
            $this->addFlash('success', $this->translator->trans('Content is featured.'));
        }

        $this->em->persist($article);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/articles/{slug}/disable', name: 'disable', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/articles/{slug}/delete', name: 'delete', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function delete(string $slug): Response
    {
        /** @var HelpCenterArticle $article */
        $article = $this->settingService->getHelpCenterArticles(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$article) {
            $this->addFlash('danger', $this->translator->trans('The article can not be found'));

            return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
        }
        if (null !== $article->getDeletedAt()) {
            $this->addFlash('danger', $this->translator->trans('Content was deleted successfully.'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Content was disabled successfully.'));
        }

        $article->setIsOnline(true);

        $this->em->persist($article);
        $this->em->flush();
        $this->em->remove($article);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/articles/{slug}/restore', name: 'restore', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function restore(string $slug): Response
    {
        /** @var HelpCenterArticle $article */
        $article = $this->settingService->getHelpCenterArticles(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$article) {
            $this->addFlash('danger', $this->translator->trans('The article can not be found'));

            return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
        }
        $article->setDeletedAt(null);

        $this->em->persist($article);
        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('Content was restored successfully.'));

        return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/articles/{slug}/show', name: 'show', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    #[Route(path: '/articles/{slug}/hide', name: 'hide', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
    public function showhide(string $slug): Response
    {
        /** @var HelpCenterArticle $article */
        $article = $this->settingService->getHelpCenterArticles(['isOnline' => 'all', 'slug' => $slug])->getQuery()->getOneOrNullResult();
        if (!$article) {
            $this->addFlash('danger', $this->translator->trans('The article can not be found'));

            return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
        }

        if (true === $article->getIsOnline()) {
            $article->setIsOnline(false);
            $this->addFlash('success', $this->translator->trans('Content is online'));
        } else {
            $article->setIsOnline(true);
            $this->addFlash('danger', $this->translator->trans('Content is offline'));
        }

        $this->em->persist($article);
        $this->em->flush();

        return $this->redirectToRoute('dashboard_admin_help_center_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
