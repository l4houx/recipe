<?php

namespace App\Twig\Components\Post;

use App\Entity\Post;
use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent(name: 'blog-search', template: 'components/post/blog-search.html.twig')]
final class BlogSearchComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly PaginatorInterface $paginator
    ) {
    }

    /**
     * @return array<Post>
     */
    public function getPosts(): array
    {
        return $this->postRepository->findBySearchQuery($this->query);
    }

    public function getPostsCount(): int
    {
        return $this->postRepository->count([]);
    }
}
