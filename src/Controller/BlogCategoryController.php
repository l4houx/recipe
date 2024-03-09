<?php

namespace App\Controller;

use App\DTO\SearchDataDTO;
use App\Entity\PostCategory;
use App\Form\SearchDataType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/blog')]
class BlogCategoryController extends AbstractController
{
    #[Route(path: '/categories/{slug}', name: 'blog_category', defaults: ['_format' => 'html'], requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    #[Route(path: '/rss.xml', name: 'blog_rss', defaults: ['_format' => 'xml'], methods: ['GET'])]
    #[Cache(smaxage: 10)]
    public function blogCategory(Request $request, string $_format, PostRepository $post, PostCategory $category): Response
    {
        $searchDataDTO = new SearchDataDTO();

        $form = $this->createForm(SearchDataType::class, $searchDataDTO)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchDataDTO->page = $request->query->getInt('page', 1);
            $pagination = $post->findBySearch($searchDataDTO);

            return $this->render('blog/blog.'.$_format.'.twig', compact('form', 'pagination', 'category'));
        }

        $pagination = $post->findPublished($request->query->getInt('page', 1), $category);

        return $this->render('blog/blog-category.html.twig', compact('form', 'pagination', 'category'));
    }
}
