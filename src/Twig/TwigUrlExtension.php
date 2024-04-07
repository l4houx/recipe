<?php

namespace App\Twig;

use App\Entity\User;
use Twig\TwigFilter;
use Twig\TwigFunction;
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
            // new TwigFunction('parent_path', $this->parentPath(...)),
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
            new TwigFilter('autolink', $this->autoLink(...)),
        ];
    }

    /*
    public function parentPath(Parent $parent): ?string
    {
        if ($parent instanceof Post) {
            return $this->urlGenerator->generate('blog_article', ['slug' => $parents->getSlug()]);
        }

        return null;
    }
    */

    public function avatarName(User $user): ?string
    {
        if (null === $user->getAvatarName()) {
            return '/images/user/default.png';
        }

        return sprintf(
            '%s?uid=%s',
            $this->uploaderHelper->asset($user, 'avatarFile'),
            $user->getUpdatedAt()?->getTimestamp() ?: 0
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
