<?php

namespace App\EventListener;

use App\Service\SettingService;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\GoogleMultilangUrlDecorator;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerUrls($event->getUrlContainer(), $event->getUrlGenerator());
    }

    /**
     * @param UrlContainerInterface $urls
     * //@param UrlGeneratorInterface $router
     */
    public function registerUrls(UrlContainerInterface $urls): void
    {
        // Register static pages urls
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('team', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('testimonial', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('login', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('forgot_password_request', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('register', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('contact', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');

        // Register blog posts urls
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('blog', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $blogPosts = $this->settingService->getBlogPosts([])->getQuery()->getResult();
        foreach ($blogPosts as $blogPost) {
            $url = new UrlConcrete($this->urlGenerator->generate('blog_article', ['slug' => $blogPost->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            // $decoratedUrl->addLink($this->urlGenerator->generate('blog_article', ['slug' => $blogPost->getSlug(), '_locale' => 'fr'], UrlGeneratorInterface::ABSOLUTE_URL), 'fr');
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register recipes urls
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('recipe_index', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $recipes = $this->settingService->getRecipes([])->getQuery()->getResult();
        foreach ($recipes as $recipe) {
            $url = new UrlConcrete($this->urlGenerator->generate('recipe_show', ['slug' => $recipe->getSlug(), 'id' => $recipe->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register help center urls
        $urls->addUrl(new UrlConcrete($this->urlGenerator->generate('help_center', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $helpCenterCategories = $this->settingService->getHelpCenterCategories([])->getQuery()->getResult();
        foreach ($helpCenterCategories as $helpCenterCategory) {
            $url = new UrlConcrete($this->urlGenerator->generate('help_center_category', ['slug' => $helpCenterCategory->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }
        $helpCenterArticles = $this->settingService->getHelpCenterArticles([])->getQuery()->getResult();
        foreach ($helpCenterArticles as $helpCenterArticle) {
            $url = new UrlConcrete($this->urlGenerator->generate('help_center_article', ['slug' => $helpCenterArticle->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register pages urls
        $pages = $this->settingService->getPages([])->getQuery()->getResult();
        foreach ($pages as $page) {
            $url = new UrlConcrete($this->urlGenerator->generate('page', ['slug' => $page->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register urls
    }
}
