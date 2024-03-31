<?php

namespace App\Service;

use App\Entity\Setting\Setting;
use App\Entity\Traits\HasRoles;
use App\Repository\Setting\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SettingService
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly EntityManagerInterface $em,
        private readonly AuthorizationCheckerInterface $authChecker,
        private readonly RequestStack $requestStack,
        private readonly KernelInterface $kernel,
        private readonly CacheItemPoolInterface $cache,
        private readonly UrlGeneratorInterface $router,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $params,
        private readonly Environment $templating,
        private readonly UrlMatcherInterface $urlMatcherInterface
    ) {
    }

    public function findAll(): array
    {
        return $this->settingRepository->findAllForTwig();
    }

    public function getValue(string $name): mixed
    {
        return $this->settingRepository->getValue($name);
    }

    // Gets a setting from the cache / db
    public function getSettings(string $name): mixed
    {
        $settingcache = $this->cache->getItem('setting_'.$name);
        if ($settingcache->isHit()) {
            return $settingcache->get();
        }

        /** @var Setting $setting */
        /** @phpstan-ignore-next-line */
        $setting = $this->em->getRepository(Setting::class)->findOneByName($name);
        if (!$setting) {
            return null;
        }

        $settingcache->set($setting->getValue());
        $this->cache->save($settingcache);

        return $setting ? ($setting->getValue()) : (null);
    }

    // Sets a option from the cache / db
    public function setSettings(string $name, $value): int
    {
        /** @var Setting $setting */
        /** @phpstan-ignore-next-line */
        $setting = $this->em->getRepository(Setting::class)->findOneByName($name);
        if ($setting) {
            $setting->setValue($value);
            $this->em->flush();
            $settingcache = $this->cache->getItem('setting_'.$name);
            $settingcache->set($value);
            $this->cache->save($settingcache);

            if ('website_name' === $name || 'website_no_reply_email' === $name || 'website_root_url' === $name) {
                $this->updateEnv(mb_strtoupper($name), $value);
            }

            return 1;
        }

        return 0;
    }

    // Updates the .env name with the choosen value
    public function updateEnv(string $name, string $value): void
    {
        if (0 == strlen($name)) {
            return;
        }

        $value = trim($value);
        if ($value == trim($value) && false !== strpos($value, ' ')) {
            $value = '"'.$value.'"';
        }

        $envFile = $this->kernel->getProjectDir().'/.env';
        $lines = file($envFile);
        $newLines = [];

        foreach ($lines as $line) {
            preg_match('/'.$name.'=/i', $line, $matches);
            if (!\count($matches)) {
                $newLines[] = $line;
                continue;
            }
            $newLine = trim($name).'='.trim($value)."\n";
            $newLines[] = $newLine;
        }

        $newContent = implode('', $newLines);
        file_put_contents($envFile, $newContent);
    }

    // Gets the value with the entered name from the .env file
    public function getEnv(string $name)
    {
        if (0 == strlen($name)) {
            return;
        }

        $envFile = $this->kernel->getProjectDir().'/.env';
        $lines = file($envFile);
        foreach ($lines as $line) {
            preg_match('/'.$name.'=/i', $line, $matches);

            if (!\count($matches)) {
                continue;
            }

            $value = trim(explode('=', $line, 2)[1]);

            return trim($value, '"');
        }

        return null;
    }

    // Generates a random string iwth a specified length
    public function generateReference(int $length): string
    {
        $reference = implode('', [
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(\chr((\ord(random_bytes(1)) & 0x0F) | 0x40)).bin2hex(random_bytes(1)),
            bin2hex(\chr((\ord(random_bytes(1)) & 0x3F) | 0x80)).bin2hex(random_bytes(1)),
            bin2hex(random_bytes(2)),
        ]);

        return mb_strlen($reference) > $length ? mb_substr($reference, 0, $length) : $reference;
    }

    // Checks if string ends with string
    public function endsWith(mixed $haystack, mixed $needle): bool
    {
        $length = mb_strlen($needle);
        if (!$length) {
            return true;
        }

        return mb_substr($haystack, -$length) === $needle;
    }

    // Get route name from path
    public function getRouteName($path = null): mixed
    {
        try {
            if ($path) {
                return $this->urlMatcherInterface->match($path)['_route'];
            }

            return $this->urlMatcherInterface->match($this->requestStack->getCurrentRequest()->getPathInfo())['_route'];
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            return null;
        }
    }

    // Shows the soft deleted entities for ROLE_SUPER_ADMIN
    public function disableSofDeleteFilterForAdmin(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker): void
    {
        $em->getFilters()->enable('softdeleteable');
        if ($authChecker->isGranted(HasRoles::APPLICATION)) {
            $em->getFilters()->disable('softdeleteable');
        }
    }

    // Returns the pages after applying the specified search criterias
    public function getPages($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';

        return $this->em->getRepository("App\Entity\Setting\Page")->getPages($slug);
    }

    // Returns the levels after applying the specified search criterias
    public function getLevels($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $keyword = \array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $limit = \array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $order = \array_key_exists('order', $criterias) ? $criterias['order'] : 'l.name';
        $sort = \array_key_exists('sort', $criterias) ? $criterias['sort'] : 'ASC';

        return $this->em->getRepository("App\Entity\Level")->getLevels($keyword, $id, $limit, $order, $sort);
    }

    

    // Returns the venues after applying the specified search criterias
    public function getVenues($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : "all";
        $country = array_key_exists('country', $criterias) ? $criterias['country'] : "all";
        $venuetypes = array_key_exists('venuetypes', $criterias) ? $criterias['venuetypes'] : "all";
        $directory = array_key_exists('directory', $criterias) ? $criterias['directory'] : "all";
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : "all";
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : "all";
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $minseatedguests = array_key_exists('minseatedguests', $criterias) ? $criterias['minseatedguests'] : "all";
        $maxseatedguests = array_key_exists('maxseatedguests', $criterias) ? $criterias['maxseatedguests'] : "all";
        $minstandingguests = array_key_exists('minstandingguests', $criterias) ? $criterias['minstandingguests'] : "all";
        $maxstandingguests = array_key_exists('maxstandingguests', $criterias) ? $criterias['maxstandingguests'] : "all";
        $restaurantEnabled = array_key_exists('restaurantEnabled', $criterias) ? $criterias['restaurantEnabled'] : "all";

        return $this->em->getRepository("App\Entity\Venue")->getVenues($restaurant, $isOnline, $keyword, $country, $venuetypes, $directory, $slug, $limit, $minseatedguests, $maxseatedguests, $minstandingguests, $maxstandingguests, $count, $restaurantEnabled);
    }

    // Returns the reviews after applying the specified search criterias
    public function getReviews($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $user = array_key_exists('user', $criterias) ? $criterias['user'] : 'all';
        $recipe = array_key_exists('recipe', $criterias) ? $criterias['recipe'] : 'all';
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $visible = array_key_exists('visible', $criterias) ? $criterias['visible'] : true;
        $rating = array_key_exists('rating', $criterias) ? $criterias['rating'] : 'all';
        $minrating = array_key_exists('minrating', $criterias) ? $criterias['minrating'] : 'all';
        $maxrating = array_key_exists('maxrating', $criterias) ? $criterias['maxrating'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\Review")->getReviews($keyword, $slug, $user, $recipe, $restaurant, $visible, $rating, $minrating, $maxrating, $limit, $count, $sort, $order);
    }

    // Returns the categories after applying the specified search criterias
    public function getCategories($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : "all";
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : "all";
        $isFeatured = array_key_exists('isFeatured', $criterias) ? $criterias['isFeatured'] : "all";
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : "all";
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : "c.name";
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : "ASC";

        return $this->em->getRepository("App\Entity\Category")->getCategories($isOnline, $keyword, $slug, $isFeatured, $limit, $sort, $order);
    }

    // Returns the recips after applying the specified search criterias
    public function getRecipes($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $category = array_key_exists('category', $criterias) ? $criterias['category'] : 'all';
        $venue = array_key_exists('venue', $criterias) ? $criterias['venue'] : 'all';
        $country = array_key_exists('country', $criterias) ? $criterias['country'] : 'all';
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $freeonly = array_key_exists('freeonly', $criterias) ? $criterias['freeonly'] : false;
        $onlineonly = array_key_exists('onlineonly', $criterias) ? $criterias['onlineonly'] : false;
        $pricemin = array_key_exists('pricemin', $criterias) ? $criterias['pricemin'] : 'all';
        $pricemax = array_key_exists('pricemax', $criterias) ? $criterias['pricemax'] : 'all';
        /*
        $audience = array_key_exists('audience', $criterias) ? $criterias['audience'] : "all";
        $startdate = array_key_exists('startdate', $criterias) ? $criterias['startdate'] : "all";
        $startdatemin = array_key_exists('startdatemin', $criterias) ? $criterias['startdatemin'] : "all";
        $startdatemax = array_key_exists('startdatemax', $criterias) ? $criterias['startdatemax'] : "all";
        if ($startdate == "today") {
            $startdate = date_format(new \DateTime, "Y-m-d");
        } elseif ($startdate == "tomorrow") {
            $startdate = date_format(date_modify(new \DateTime, "+1 day"), "Y-m-d");
        } elseif ($startdate == "thisweekend") {
            $startdate = "all";
            $startdatemin = date_format(date_modify(new \DateTime, "saturday this week"), "Y-m-d");
            $startdatemax = date_format(date_modify(new \DateTime, "sunday this week"), "Y-m-d");
        } elseif ($startdate == "thisweek") {
            $startdate = "all";
            $startdatemin = date_format(date_modify(new \DateTime, "monday this week"), "Y-m-d");
            $startdatemax = date_format(date_modify(new \DateTime, "sunday this week"), "Y-m-d");
        } elseif ($startdate == "nextweek") {
            $startdate = "all";
            $startdatemin = date_format(date_modify(new \DateTime, "monday next week"), "Y-m-d");
            $startdatemax = date_format(date_modify(new \DateTime, "sunday next week"), "Y-m-d");
        } elseif ($startdate == "thismonth") {
            $startdate = "all";
            $startdatemin = date_format(new \DateTime, "Y-m-01");
            $startdatemax = date_format(new \DateTime, "Y-m-t");
        } elseif ($startdate == "nextmonth") {
            $startdate = "all";
            $startdatemin = date_format(date_modify(new \DateTime, "+1 month"), "Y-m-01");
            $startdatemax = date_format(date_modify(new \DateTime, "+1 month"), "Y-m-t");
        }
        */
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $otherthan = array_key_exists('otherthan', $criterias) ? $criterias['otherthan'] : 'all';
        $notId = array_key_exists('notId', $criterias) ? $criterias['notId'] : 'all';
        $localonly = array_key_exists('localonly', $criterias) ? $criterias['localonly'] : false;
        $location = array_key_exists('location', $criterias) ? $criterias['location'] : 'all';
        if ('1' == $localonly) {
            $country = $this->locateUser()['country']->getSlug();
        }
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $elapsed = array_key_exists('elapsed', $criterias) ? $criterias['elapsed'] : false;
        $restaurantEnabled = array_key_exists('restaurantEnabled', $criterias) ? $criterias['restaurantEnabled'] : true;
        $addedtofavoritesby = array_key_exists('addedtofavoritesby', $criterias) ? $criterias['addedtofavoritesby'] : 'all';
        $onsalebypos = array_key_exists('onsalebypos', $criterias) ? $criterias['onsalebypos'] : 'all';
        $canbescannedby = array_key_exists('canbescannedby', $criterias) ? $criterias['canbescannedby'] : 'all';
        $isOnHomepageSlider = array_key_exists('isOnHomepageSlider', $criterias) ? $criterias['isOnHomepageSlider'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'recipedates.startdate';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;

        return $this->em->getRepository("App\Entity\Recipe")->getRecipes($category, $venue, $country, $location, $restaurant, $keyword, $slug, $freeonly, $onlineonly, $pricemin, $pricemax/* , $audience, $startdate, $startdatemin, $startdatemax */, $isOnline, $elapsed, $restaurantEnabled, $addedtofavoritesby, $onsalebypos, $canbescannedby, $isOnHomepageSlider, $otherthan, $notId, $sort, $order, $limit, $count);
    }

    /*
    public function getRecipes($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';

        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $user = array_key_exists('user', $criterias) ? $criterias['user'] : 'all';
        $userEnabled = array_key_exists('userEnabled', $criterias) ? $criterias['userEnabled'] : true;
        $addedtofavoritesby = array_key_exists('addedtofavoritesby', $criterias) ? $criterias['addedtofavoritesby'] : 'all';
        $isOnHomepageSlider = array_key_exists('isOnHomepageSlider', $criterias) ? $criterias['isOnHomepageSlider'] : 'all';
        $otherthan = array_key_exists('otherthan', $criterias) ? $criterias['otherthan'] : 'all';

        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'r.title';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;

        return $this->em->getRepository("App\Entity\Recipe")->getRecipes($keyword, $slug, $isOnline, $user, $userEnabled, $addedtofavoritesby, $isOnHomepageSlider, $otherthan, $sort, $order, $limit, $count);
    }
    */

    // Returns the users after applying the specified search criterias
    public function getUsers($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        //$role = array_key_exists('role', $criterias) ? $criterias['role'] : 'all';
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $createdbyrestaurantslug = array_key_exists('createdbyrestaurantslug', $criterias) ? $criterias['createdbyrestaurantslug'] : 'all';
        $restaurantname = array_key_exists('restaurantname', $criterias) ? $criterias['restaurantname'] : 'all';
        $restaurantslug = array_key_exists('restaurantslug', $criterias) ? $criterias['restaurantslug'] : 'all';
        $username = array_key_exists('username', $criterias) ? $criterias['username'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $email = array_key_exists('email', $criterias) ? $criterias['email'] : 'all';
        $firstname = array_key_exists('firstname', $criterias) ? $criterias['firstname'] : 'all';
        $lastname = array_key_exists('lastname', $criterias) ? $criterias['lastname'] : 'all';
        $isVerified = array_key_exists('isVerified', $criterias) ? $criterias['isVerified'] : true;
        $isSuspended = array_key_exists('isSuspended', $criterias) ? $criterias['isSuspended'] : false;
        $isOnHomepageSlider = array_key_exists('isOnHomepageSlider', $criterias) ? $criterias['isOnHomepageSlider'] : 'all';
        $countryslug = array_key_exists('countryslug', $criterias) ? $criterias['countryslug'] : 'all';
        $followedby = array_key_exists('followedby', $criterias) ? $criterias['followedby'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'u.createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;

        return $this->em->getRepository("App\Entity\User")->getUsers(/*$role, */$keyword, $createdbyrestaurantslug, $restaurantname, $restaurantslug, $username, $slug, $email, $firstname, $lastname, $isVerified, $isSuspended, $isOnHomepageSlider, $countryslug, $followedby, $limit, $sort, $order, $count);
    }

    // Returns the testimonials after applying the specified search criterias
    public function getTestimonials($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $user = array_key_exists('user', $criterias) ? $criterias['user'] : 'all';
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $rating = array_key_exists('rating', $criterias) ? $criterias['rating'] : 'all';
        $minrating = array_key_exists('minrating', $criterias) ? $criterias['minrating'] : 'all';
        $maxrating = array_key_exists('maxrating', $criterias) ? $criterias['maxrating'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\Testimonial")->getTestimonials($keyword, $id, $user, $isOnline, $rating, $minrating, $maxrating, $limit, $count, $sort, $order);
    }

    // Returns the help center categories after applying the specified search criterias
    public function getHelpCenterCategories($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $parent = \array_key_exists('parent', $criterias) ? $criterias['parent'] : 'all';
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $keyword = \array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = \array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = \array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $order = \array_key_exists('order', $criterias) ? $criterias['order'] : 'c.name';
        $sort = \array_key_exists('sort', $criterias) ? $criterias['sort'] : 'ASC';

        return $this->em->getRepository("App\Entity\HelpCenterCategory")->getHelpCenterCategories($parent, $isOnline, $keyword, $slug, $limit, $order, $sort);
    }

    // Returns the help center articles after applying the specified search criterias
    public function getHelpCenterArticles($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $selecttags = \array_key_exists('selecttags', $criterias) ? $criterias['selecttags'] : false;
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $isFeatured = \array_key_exists('isFeatured', $criterias) ? $criterias['isFeatured'] : 'all';
        $keyword = \array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = \array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $category = \array_key_exists('category', $criterias) ? $criterias['category'] : 'all';
        $limit = \array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $otherthan = \array_key_exists('otherthan', $criterias) ? $criterias['otherthan'] : 'all';
        $sort = \array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = \array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\HelpCenterArticle")->getHelpCenterArticles($selecttags, $isOnline, $isFeatured, $keyword, $slug, $category, $limit, $sort, $order, $otherthan);
    }

    // Returns the blog posts after applying the specified search criterias
    public function getBlogPosts($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $selecttags = array_key_exists('selecttags', $criterias) ? $criterias['selecttags'] : false;
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $category = array_key_exists('category', $criterias) ? $criterias['category'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $otherthan = array_key_exists('otherthan', $criterias) ? $criterias['otherthan'] : 'all';
        $sort = array_key_exists('order', $criterias) ? $criterias['order'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\Post")->getBlogPosts($selecttags, $isOnline, $keyword, $slug, $category, $limit, $sort, $order, $otherthan);
    }

    // Returns the blog posts categories after applying the specified search criterias
    public function getBlogPostCategories($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $parent = \array_key_exists('parent', $criterias) ? $criterias['parent'] : 'all';
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $order = \array_key_exists('order', $criterias) ? $criterias['order'] : 'c.name';
        $sort = \array_key_exists('sort', $criterias) ? $criterias['sort'] : 'ASC';

        return $this->em->getRepository("App\Entity\PostCategory")->getBlogPostCategories($parent, $isOnline, $keyword, $slug, $limit, $order, $sort);
    }

    // Returns the currencies
    public function getCurrencies(mixed $criterias): QueryBuilder
    {
        $ccy = \array_key_exists('ccy', $criterias) ? $criterias['ccy'] : 'all';
        $symbol = \array_key_exists('symbol', $criterias) ? $criterias['symbol'] : 'all';

        return $this->em->getRepository("App\Entity\Setting\Currency")->getCurrencies($ccy, $symbol);
    }

    // Returns the current protocol of the current request
    public function getCurrentRequestProtocol(): string
    {
        return $this->requestStack->getCurrentRequest()->getScheme();
    }

    // Returns the layout settings entity to be used in the twig templates
    public function getAppLayoutSettings()
    {
        $appLayoutSettings = $this->em->getRepository("App\Entity\Setting\AppLayoutSetting")->find(1);

        return $appLayoutSettings;
    }

    // Returns the languages after applying the specified search criterias
    public function getLanguages($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';

        return $this->em->getRepository("App\Entity\Setting\Language")->getLanguages($isOnline, $keyword, $slug, $limit, $sort, $order);
    }

    // Returns the countries after applying the specified search criterias
    public function getCountries($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : false;
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $isocode = array_key_exists('isocode', $criterias) ? $criterias['isocode'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';

        return $this->em->getRepository("App\Entity\Country")->getCountries($id, $isOnline, $keyword, $isocode, $slug, $limit, $sort, $order);
    }

    // Returns the current user location
    public function locateUser(): ?array
    {
        $request = $this->requestStack->getCurrentRequest();
        $reader = new Reader($this->kernel->getProjectDir().'/assets/vendor/geolite/GeoLite2-City.mmdb');

        try {
            $ip = '128.101.101.101';
            if ('prod' == $this->kernel->getEnvironment()) {
                $ip = $request->getClientIp();
            }
            $record = $reader->city($ip);
            $countryentity = $this->getCountries(['isocode' => $record->country->isoCode])->getQuery()->getOneOrNullResult();
        } catch (AddressNotFoundException $ex) {
            return null;
            // return new Response($this->translator->trans("It wasn't possible to retrieve information about the provided IP"));
        }

        return ['record' => $record, 'country' => $countryentity];
    }

    // Changes the link locale
    public function changeLinkLocale($newLocale, $link)
    {
        if ($link == "categories_dropdown" || $link == "footer_categories_section") {
            return $link;
        }
        if (strpos($link, "/en/") !== false) {
            return str_replace("/en/", "/" . $newLocale . "/", $link);
        } elseif (strpos($link, "/fr/") !== false) {
            return str_replace("/fr/", "/" . $newLocale . "/", $link);
        } elseif (strpos($link, "/ar/") !== false) {
            return str_replace("/ar/", "/" . $newLocale . "/", $link);
        } elseif (strpos($link, "/es/") !== false) {
            return str_replace("/es/", "/" . $newLocale . "/", $link);
        } elseif (strpos($link, "/pt/") !== false) {
            return str_replace("/pt/", "/" . $newLocale . "/", $link);
        } elseif (strpos($link, "/de/") !== false) {
            return str_replace("/de/", "/" . $newLocale . "/", $link);
        } elseif (strpos($link, "/it/") !== false) {
            return str_replace("/it/", "/" . $newLocale . "/", $link);
        } elseif (strpos($link, "/br/") !== false) {
            return str_replace("/br/", "/" . $newLocale . "/", $link);
        } elseif ($this->endsWith($link, "/en")) {
            return str_replace("/en", "/" . $newLocale, $link);
        } elseif ($this->endsWith($link, "/fr")) {
            return str_replace("/fr", "/" . $newLocale, $link);
        } elseif ($this->endsWith($link, "/ar")) {
            return str_replace("/ar", "/" . $newLocale, $link);
        } elseif ($this->endsWith($link, "/es")) {
            return str_replace("/es", "/" . $newLocale, $link);
        } elseif ($this->endsWith($link, "/pt")) {
            return str_replace("/pt", "/" . $newLocale, $link);
        } elseif ($this->endsWith($link, "/de")) {
            return str_replace("/de", "/" . $newLocale, $link);
        } elseif ($this->endsWith($link, "/it")) {
            return str_replace("/it", "/" . $newLocale, $link);
        } elseif ($this->endsWith($link, "/br")) {
            return str_replace("/br", "/" . $newLocale, $link);
        }
        return 'x';
    }
}
