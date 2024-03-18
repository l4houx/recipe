<?php

namespace App\Controller;

use App\Entity\Post;
use App\DTO\SearchDataDTO;
use App\Form\SearchDataType;
use App\Repository\PostRepository;
use App\Repository\KeywordRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/blog')]
class BlogController extends AbstractController
{
    #[Route(path: '/', name: 'blog_list', defaults: ['_format' => 'html'], methods: ['GET'])]
    #[Route(path: '/rss.xml', name: 'blog_rss', defaults: ['_format' => 'xml'], methods: ['GET'])]
    #[Cache(smaxage: 10)]
    public function blogList(Request $request, string $_format, PostRepository $post): Response
    {
        $searchDataDTO = new SearchDataDTO();

        $form = $this->createForm(SearchDataType::class, $searchDataDTO)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchDataDTO->page = $request->query->getInt('page', 1);
            $pagination = $post->findBySearch($searchDataDTO);

            return $this->render('blog/blog.'.$_format.'.twig', compact('form', 'pagination'));
        }

        $pagination = $post->findPublished($request->query->getInt('page', 1));

        return $this->render('blog/blog.'.$_format.'.twig', compact('form', 'pagination'));
    }

    #[Route(path: '/{slug}', name: 'blog_article', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function blogArticle(
        Request $request,
        Post $post,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): Response {
        if (!$post) {
            $this->addFlash('secondary', $translator->trans('The article not be found'));
            return $this->redirectToRoute('blog_list');
        }

        $post->viewed();
        $em->persist($post);
        $em->flush();

        return $this->render('blog/blog-article.html.twig', compact('post'));
    }

    #[Route(path: '/search', name: 'blog_search', methods: ['GET'])]
    public function blogSearch(Request $request): Response
    {
        return $this->render('blog/blog-search.html.twig', ['query' => (string) $request->query->get('q', '')]);
    }

    #[Route('/featured-content', name: 'blog_featured_content', methods: ['GET'], priority: 10)]
    public function featuredContent(
        PostRepository $postRepository,
        PostCategoryRepository $postCategoryRepository,
        KeywordRepository $keywordRepository,
        int $maxResults = 4
    ): Response {
        // Total
        $totalPosts = $postRepository->count([]);
        // Post
        $latestPosts = $postRepository->findBy(['isOnline' => true], ['createdAt' => 'DESC'], $maxResults);
        // Comment
        $mostCommentedPosts = $postRepository->findMostCommented($maxResults);
        // Category
        $postCategories = $postCategoryRepository->findBy([], ['createdAt' => 'DESC'], $maxResults);
        // Keyword
        $postKeywords = $keywordRepository->findBy([], ['createdAt' => 'DESC'], $maxResults);

        return $this->render(
            'widget/_featured_content.html.twig',
            compact('totalPosts', 'latestPosts', 'mostCommentedPosts', 'postCategories', 'postKeywords')
        )->setSharedMaxAge(50);
    }
}
