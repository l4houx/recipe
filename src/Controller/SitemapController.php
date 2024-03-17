<?php

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitemapController extends AbstractController
{
    #[Route(path: '/sitemap.xml', name: 'sitemap', methods: [Request::METHOD_GET])]
    public function sitemap(
        Request $request,
        PostRepository $postRepository,
        PageRepository $pageRepository
    ): Response {
        $hostname = $request->getSchemeAndHttpHost();

        // Register static pages urls
        $urls = [];

        $urls[] = ['loc' => $this->generateUrl('home')];
        $urls[] = ['loc' => $this->generateUrl('blog_list')];
        $urls[] = ['loc' => $this->generateUrl('contact')];
        $urls[] = ['loc' => $this->generateUrl('team')];
        $urls[] = ['loc' => $this->generateUrl('faq')];
        $urls[] = ['loc' => $this->generateUrl('help_center')];
        $urls[] = ['loc' => $this->generateUrl('testimonial')];
        $urls[] = ['loc' => $this->generateUrl('login')];
        $urls[] = ['loc' => $this->generateUrl('register')];

        // dd($urls);

        // Register pages urls
        foreach ($pageRepository->findAll() as $page) {
            $urls[] = [
                'loc' => $this->generateUrl('page', ['slug' => $page->getSlug()]),
                'lastmod' => $page->getCreatedAt()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => 0.9,
            ];
        }

        // Register posts urls
        $posts = $postRepository->findBy([], ['publishedAt' => 'DESC']);
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $this->generateUrl('blog_article', ['slug' => $post->getSlug()]),
                'lastmod' => $post->getUpdatedAt()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => 0.9,
            ];
        }

        $response = $this->render('pages/sitemap.html.twig', compact('urls', 'hostname'));

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
