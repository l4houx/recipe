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
        private readonly SettingService $settingService
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

    public function registerUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        // Register static pages urls
        $urls->addUrl(new UrlConcrete($router->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($router->generate('team', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($router->generate('testimonial', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($router->generate('login', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($router->generate('forgot_password_request', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($router->generate('register', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $urls->addUrl(new UrlConcrete($router->generate('contact', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');

        // Register posts urls
        $urls->addUrl(new UrlConcrete($router->generate('post', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $posts = $this->settingService->getBlogPosts([])->getQuery()->getResult();
        foreach ($posts as $post) {
            $url = new UrlConcrete($router->generate('post_article', ['slug' => $post->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register recipes urls
        $urls->addUrl(new UrlConcrete($router->generate('recipes', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $recipes = $this->settingService->getRecipes([])->getQuery()->getResult();
        foreach ($recipes as $recipe) {
            $url = new UrlConcrete($router->generate('recipe', ['slug' => $recipe->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register help center urls
        $urls->addUrl(new UrlConcrete($router->generate('help_center', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $helpCenterCategories = $this->settingService->getHelpCenterCategories([])->getQuery()->getResult();
        foreach ($helpCenterCategories as $helpCenterCategory) {
            $url = new UrlConcrete($router->generate('help_center_category', ['slug' => $helpCenterCategory->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }
        $helpCenterArticles = $this->settingService->getHelpCenterArticles([])->getQuery()->getResult();
        foreach ($helpCenterArticles as $helpCenterArticle) {
            $url = new UrlConcrete($router->generate('help_center_article', ['slug' => $helpCenterArticle->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register pages urls
        $pages = $this->settingService->getPages([])->getQuery()->getResult();
        foreach ($pages as $page) {
            $url = new UrlConcrete($router->generate('page', ['slug' => $page->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register restaurants urls
        $restaurants = $this->settingService->getUsers(['role' => 'restaurant'])->getQuery()->getResult();
        foreach ($restaurants as $restaurant) {
            $url = new UrlConcrete($router->generate('restaurant', ['slug' => $restaurant->getRestaurant()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register venues urls
        $urls->addUrl(new UrlConcrete($router->generate('venues', [], UrlGeneratorInterface::ABSOLUTE_URL)), 'default');
        $venues = $this->settingService->getVenues(['isListedondirectory' => true])->getQuery()->getResult();
        foreach ($venues as $venue) {
            $url = new UrlConcrete($router->generate('venue', ['slug' => $venue->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
            $decoratedUrl = new GoogleMultilangUrlDecorator($url);
            $urls->addUrl($decoratedUrl, 'default');
        }

        // Register urls
    }
}
