<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SettingService;
use App\DTO\HelpCenterSupportDTO;
use App\Entity\HelpCenterArticle;
use App\Entity\HelpCenterCategory;
use App\Form\HelpCenterSupportFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Event\HelpCenterSupportRequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\HelpCenterArticleRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HelpCenterCategoryRepository;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/help-center')]
class HelpCenterController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SettingService $settingService,
        private readonly HelpCenterArticleRepository $helpCenterArticleRepository,
        private readonly HelpCenterCategoryRepository $helpCenterCategoryRepository
    ) {
    }

    #[Route('/', name: 'help_center', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('helpCenter/index.html.twig');
    }

    #[Route('/{slug}-{id}', name: 'help_center_category', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT, 'slug' => Requirement::ASCII_SLUG])]
    public function category(Request $request, string $slug, int $id): Response
    {
        ///** @var HelpCenterCategory $category */
        //$category = $this->settingService->getHelpCenterCategories(['slug' => $slug])->getQuery()->getOneOrNullResult();


        $category = $this->helpCenterCategoryRepository->find($id);

        if ($category->getSlug() !== $slug) {
            return $this->redirectToRoute('help_center_category', [
                'id' => $category->getId(),
                'slug' => $category->getSlug(),
            ], 301);
        }

        if (!$category) {
            $this->addFlash('secondary', $this->translator->trans('The category not be found'));

            return $this->redirectToRoute('help_center');
        }

        return $this->render('helpCenter/category.html.twig', compact('category'));
    }

    #[Route('/article/{slug}-{id}', name: 'help_center_article', methods: ['GET'], requirements: ['id' => Requirement::POSITIVE_INT, 'slug' => Requirement::ASCII_SLUG])]
    public function article(Request $request, string $slug, int $id, EntityManagerInterface $em): Response
    {
        ///** @var HelpCenterArticle $article */
        //$article = $this->settingService->getHelpCenterArticles(['slug' => $slug])->getQuery()->getOneOrNullResult();


        $article = $this->helpCenterArticleRepository->find($id);

        if ($article->getSlug() !== $slug) {
            return $this->redirectToRoute('help_center_article', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ], 301);
        }

        if (!$article) {
            $this->addFlash('secondary', $this->translator->trans('The article not be found'));
            return $this->redirectToRoute('help_center');
        }

        $article->viewed();
        $em->persist($article);
        $em->flush();

        return $this->render('helpCenter/article.html.twig', compact('article'));
    }

    #[Route('/support', name: 'help_center_support', methods: ['GET', 'POST'])]
    public function support(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $data = new HelpCenterSupportDTO();

        $form = $this->createForm(HelpCenterSupportFormType::class, $data)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventDispatcher->dispatch(new HelpCenterSupportRequestEvent($data));
            $this->addFlash('success', $this->translator->trans('Your message has been sent successfully, thank you.'));

            return $this->redirectToRoute('help_center_support', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('helpCenter/support.html.twig', compact('data', 'form'));
    }
}
