<?php

namespace App\Feed;

use FeedIo\Feed;
use FeedIo\Feed\Item;
use App\Entity\Recipe;
use FeedIo\FeedInterface;
use FeedIo\Feed\Item\Media;
use FeedIo\Feed\Node\Category;
use App\Service\SettingService;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Debril\RssAtomBundle\Provider\FeedProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Debril\RssAtomBundle\Exception\FeedException\FeedNotFoundException;

class Provider implements FeedProviderInterface
{
    public function __construct(
        protected readonly SettingService $settingService,
        protected readonly Packages $packages,
        protected readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * @param array $options
     * @return FeedInterface
     * @throws FeedNotFoundException
     */
    public function getFeed(Request $request): FeedInterface
    {
        // Feed
        $feed = new Feed();
        $feed->setTitle($this->settingService->getSettings('feed_name'));
        $feed->setLink($this->settingService->getSettings('website_url').'/rss');
        $feed->setDescription($this->settingService->getSettings('feed_description'));

        $lastRecipeUpdatedDate = null;

        foreach ($this->getItems() as $item) {
            $lastRecipeUpdatedDate = is_null($lastRecipeUpdatedDate) ? $item->getLastModified() : $lastRecipeUpdatedDate;
            $feed->add($item);
        }

        $lastRecipeUpdatedDate = is_null($lastRecipeUpdatedDate) ? new \DateTime() : $lastRecipeUpdatedDate;
        $feed->setLastModified($lastRecipeUpdatedDate);

        return $feed;
    }

    protected function getItems()
    {
        /** @var Recipe $recipe */
        foreach ($this->settingService->getRecipes(['limit' => $this->settingService->getSettings('feed_recipes_limit')])->getQuery()->getResult() as $recipe) {
            // Item
            $item = new Item();
            $item->setTitle($recipe->getTitle());
            $item->getAuthor($recipe->getRestaurant()->getName());
            $item->setLink($this->router->generate('recipe', ['slug' => $recipe->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));

            // Media
            $media = new Media();
            $media->setUrl($this->settingService->getSettings('website_url').$this->packages->getUrl($recipe->getImagePath()));
            $item->addMedia($media);

            // Category
            $category = new Category();
            $category->setLabel($recipe->getCategory()->getName());
            $item->addCategory($category);
            $item->setLastModified($recipe->getUpdatedAt());

            yield $item;
        }
    }
}
