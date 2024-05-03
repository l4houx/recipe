<?php

namespace App\Twig;

use App\Entity\Post;
use App\Entity\User;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Restaurant;
use Twig\Extension\AbstractExtension;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface;

class TwigUrlExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UploaderHelperInterface $uploaderHelper,
        private readonly SerializerInterface $serializer
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('post_path', $this->postPath(...)),
            new TwigFunction('path', $this->pathFor(...)),
            new TwigFunction('url', $this->urlFor(...)),
        ];
    }

    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('avatarName', $this->avatarName(...)),
            new TwigFilter('logoName', $this->logoName(...)),
            new TwigFilter('coverName', $this->coverName(...)),
            new TwigFilter('autolink', $this->autoLink(...)),
        ];
    }

    public function postPath(Post $post): ?string
    {
        if ($post instanceof Post) {
            return $this->urlGenerator->generate('post_article', ['slug' => $post->getSlug()]);
        }

        return null;
    }

    public function avatarName(User $user): ?string
    {
        if (null === $user->getAvatarName()) {
            return '/uploads/user/default.png';
        }

        return sprintf(
            '%s?uid=%s',
            $this->uploaderHelper->asset($user, 'avatarFile'),
            $user->getUpdatedAt()?->getTimestamp() ?: 0
        );
    }

    public function logoName(Restaurant $restaurant): ?string
    {
        if (null === $restaurant->getLogoName()) {
            return '/uploads/restaurant/default.png';
        }

        return sprintf(
            '%s?uid=%s',
            $this->uploaderHelper->asset($restaurant, 'logoFile'),
            $restaurant->getUpdatedAt()?->getTimestamp() ?: 0
        );
    }

    public function coverName(Restaurant $restaurant): ?string
    {
        if (null === $restaurant->getCoverName()) {
            return '/uploads/restaurant/covers/default.png';
        }

        return sprintf(
            '%s?uid=%s',
            $this->uploaderHelper->asset($restaurant, 'coverFile'),
            $restaurant->getUpdatedAt()?->getTimestamp() ?: 0
        );
    }

    /**
     * @param string|object $path
     */
    public function pathFor($path, array $params = []): string
    {
        if (\is_string($path)) {
            return $this->urlGenerator->generate($path, $params);
        }

        return $this->serializer->serialize($path, 'path', ['url' => false]);
    }

    /**
     * @param string|object $path
     */
    public function urlFor($path, array $params = []): string
    {
        if (\is_string($path)) {
            return $this->urlGenerator->generate(
                $path,
                $params,
                \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->serializer->serialize($path, 'path', ['url' => true]);
    }

    public function autoLink(string $string): string
    {
        $regexp = '/(<a.*?>)?(https?:)?(\/\/)(\w+\.)?(\w+\.[\w\/\-_.~&=?]+)(<\/a>)?/i';
        $anchor = '<a href="%s//%s" target="_blank" rel="noopener noreferrer">%s</a>';

        preg_match_all($regexp, $string, $matches, \PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (empty($match[1]) && empty($match[6])) {
                $protocol = $match[2] ?: 'https:';
                $replace = sprintf($anchor, $protocol, $match[5], $match[0]);
                $string = str_replace($match[0], $replace, $string);
            }
        }

        return $string;
    }
}
