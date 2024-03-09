<?php

namespace App\Controller;

use App\Entity\Keyword;
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
class BlogKeywordController extends AbstractController
{
    #[Route(path: '/keywords/{slug}', name: 'blog_keyword', defaults: ['_format' => 'html'], requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    #[Route(path: '/rss.xml', name: 'blog_rss', defaults: ['_format' => 'xml'], methods: ['GET'])]
    #[Cache(smaxage: 10)]
    public function blogKeyword(Request $request, string $_format, PostRepository $post, Keyword $keyword): Response
    {
        $searchDataDTO = new SearchDataDTO();

        $form = $this->createForm(SearchDataType::class, $searchDataDTO)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchDataDTO->page = $request->query->getInt('page', 1);
            $pagination = $post->findBySearch($searchDataDTO);

            return $this->render('blog/blog.'.$_format.'.twig', compact('form', 'pagination', 'keyword'));
        }

        $pagination = $post->findPublished($request->query->getInt('page', 1), null, $keyword);

        return $this->render('blog/blog-keyword.html.twig', compact('form', 'pagination', 'keyword'));
    }
}
