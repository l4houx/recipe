<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Venue;
use Twig\Environment;
use App\Entity\Recipe;
use App\Entity\Category;
use App\Entity\Restaurant;
use GeoIp2\Database\Reader;
use App\Entity\OrderElement;
use App\Entity\Setting\Page;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Setting\Setting;
use App\Entity\Traits\HasRoles;
use App\Entity\HelpCenterArticle;
use App\Entity\OrderSubscription;
use App\Entity\HelpCenterCategory;
use Symfony\Component\Mime\Address;
use Psr\Cache\CacheItemPoolInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Repository\Setting\SettingRepository;
use Symfony\Component\Mailer\MailerInterface;
use GeoIp2\Exception\AddressNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

    // Redirects to the referer page when available, if not, redirects to the dashboard index
    public function redirectToReferer(mixed $alt = null): RedirectResponse
    {
        if ($this->requestStack->getCurrentRequest()->headers->get('referer')) {
            return new RedirectResponse($this->requestStack->getCurrentRequest()->headers->get('referer'));
        } else {
            if ($alt) {
                if ($this->authChecker->isGranted(HasRoles::ADMINAPPLICATION)) {
                    return new RedirectResponse($this->router->generate('dashboard_admin_' . $alt));
                } elseif ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
                    return new RedirectResponse($this->router->generate('dashboard_restaurant_' . $alt));
                } elseif ($this->authChecker->isGranted(HasRoles::CREATOR)) {
                    return new RedirectResponse($this->router->generate('dashboard_creator_' . $alt));
                } elseif ($this->authChecker->isGranted(HasRoles::POINTOFSALE)) {
                    return new RedirectResponse($this->router->generate('dashboard_pointofsale_' . $alt));
                } else {
                    return new RedirectResponse($this->router->generate($alt));
                }
            } else {
                return new RedirectResponse($this->router->generate('dashboard_main'));
            }
        }
    }

    // Shows the soft deleted entities for ROLE_SUPER_ADMIN
    public function disableSofDeleteFilterForAdmin(EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker): void
    {
        $em->getFilters()->enable('softdeleteable');
        if ($authChecker->isGranted(HasRoles::ADMINAPPLICATION)) {
            $em->getFilters()->disable('softdeleteable');
        }
    }

    // Returns the applications after applying the specified search criterias
    public function getApplications($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        //$isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $user = array_key_exists('user', $criterias) ? $criterias['user'] : 'all';
        $name = array_key_exists('name', $criterias) ? $criterias['name'] : 'name';
        $token = array_key_exists('token', $criterias) ? $criterias['token'] : 'token';
        $tickets = array_key_exists('tickets', $criterias) ? $criterias['tickets'] : 'tickets';
        $roles = array_key_exists('roles', $criterias) ? $criterias['roles'] : 'roles';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\Application")->getApplications($keyword, $id, $user, $name, $token, $tickets, $roles, $limit, $count, $sort, $order);
    }

    // Returns the comments after applying the specified search criterias
    public function getComments($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $user = array_key_exists('user', $criterias) ? $criterias['user'] : 'all';
        $isApproved = array_key_exists('isApproved', $criterias) ? $criterias['isApproved'] : true;
        $isRGPD = array_key_exists('isRGPD', $criterias) ? $criterias['isRGPD'] : 'all';
        $ip = \array_key_exists('ip', $criterias) ? $criterias['ip'] : 'all';
        $post = array_key_exists('post', $criterias) ? $criterias['post'] : 'all';
        $venue = array_key_exists('venue', $criterias) ? $criterias['venue'] : 'all';
        $parent = array_key_exists('parent', $criterias) ? $criterias['parent'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'publishedAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\Comment")->getComments($keyword, $id, $user, $isApproved, $isRGPD, $ip, $post, $venue, $parent, $limit, $count, $sort, $order);
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
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';

        return $this->em->getRepository("App\Entity\Level")->getLevels($keyword, $id, $limit, $order, $sort);
    }

    // Returns the status after applying the specified search criterias
    public function getStatus($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $keyword = \array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $limit = \array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';

        return $this->em->getRepository("App\Entity\Status")->getStatus($keyword, $id, $limit, $order, $sort);
    }

    // Returns the audiences after applying the specified search criterias
    public function getAudiences($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'a.name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';

        return $this->em->getRepository("App\Entity\Audience")->getAudiences($isOnline, $keyword, $slug, $limit, $sort, $order);
    }

    // Returns the amenities after applying the specified search criterias
    public function getAmenities($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'a.name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';

        return $this->em->getRepository("App\Entity\Amenity")->getAmenities($isOnline, $keyword, $slug, $limit, $sort, $order);
    }

    // Returns the venues types after applying the specified search criterias
    public function getVenuesTypes($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'v.name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';
        $hasvenues = array_key_exists('hasvenues', $criterias) ? $criterias['hasvenues'] : 'all';

        return $this->em->getRepository("App\Entity\VenueType")->getVenuesTypes($isOnline, $keyword, $slug, $limit, $sort, $order, $hasvenues);
    }

    // Returns the venues seatings plans after applying the specified search criterias
    public function getVenuesSeatingPlans($criterias): QueryBuilder
    {
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $venue = array_key_exists('venue', $criterias) ? $criterias['venue'] : 'all';
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;

        return $this->em->getRepository("App\Entity\VenueSeatingPlan")->getVenuesSeatingPlans($id, $venue, $restaurant, $slug, $limit, $count);
    }

    // Returns the venues after applying the specified search criterias
    public function getVenues($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $country = array_key_exists('country', $criterias) ? $criterias['country'] : 'all';
        $venuetypes = array_key_exists('venuetypes', $criterias) ? $criterias['venuetypes'] : 'all';
        $directory = array_key_exists('directory', $criterias) ? $criterias['directory'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $minseatedguests = array_key_exists('minseatedguests', $criterias) ? $criterias['minseatedguests'] : 'all';
        $maxseatedguests = array_key_exists('maxseatedguests', $criterias) ? $criterias['maxseatedguests'] : 'all';
        $minstandingguests = array_key_exists('minstandingguests', $criterias) ? $criterias['minstandingguests'] : 'all';
        $maxstandingguests = array_key_exists('maxstandingguests', $criterias) ? $criterias['maxstandingguests'] : 'all';

        return $this->em->getRepository("App\Entity\Venue")->getVenues($restaurant, $isOnline, $keyword, $country, $venuetypes, $directory, $slug, $limit, $minseatedguests, $maxseatedguests, $minstandingguests, $maxstandingguests, $count);
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
        $isVisible = array_key_exists('isVisible', $criterias) ? $criterias['isVisible'] : true;
        $rating = array_key_exists('rating', $criterias) ? $criterias['rating'] : 'all';
        $minrating = array_key_exists('minrating', $criterias) ? $criterias['minrating'] : 'all';
        $maxrating = array_key_exists('maxrating', $criterias) ? $criterias['maxrating'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\Review")->getReviews($keyword, $slug, $user, $recipe, $restaurant, $isVisible, $rating, $minrating, $maxrating, $limit, $count, $sort, $order);
    }

    // Returns the categories after applying the specified search criterias
    public function getCategories($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $isFeatured = array_key_exists('isFeatured', $criterias) ? $criterias['isFeatured'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'c.name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';

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

        return $this->em->getRepository("App\Entity\Recipe")->getRecipes($category, $venue, $country, $location, $restaurant, $keyword, $slug, $freeonly, $onlineonly, $pricemin, $pricemax, $audience, $startdate, $startdatemin, $startdatemax, $isOnline, $elapsed, $restaurantEnabled, $addedtofavoritesby, $onsalebypos, $canbescannedby, $isOnHomepageSlider, $otherthan, $notId, $sort, $order, $limit, $count);
    }

    // Returns the recipe dates after applying the specified search criterias
    public function getRecipeDates($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $reference = array_key_exists('reference', $criterias) ? $criterias['reference'] : 'all';
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $recipe = array_key_exists('recipe', $criterias) ? $criterias['recipe'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;

        return $this->em->getRepository("App\Entity\RecipeDate")->getRecipeDates($reference, $restaurant, $recipe, $limit, $count);
    }

    // Returns the recipe subscriptions after applying the specified search criterias
    public function getRecipeSubscriptions($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $reference = array_key_exists('reference', $criterias) ? $criterias['reference'] : 'all';
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $recipe = array_key_exists('recipe', $criterias) ? $criterias['recipe'] : 'all';
        $recipeDate = array_key_exists('recipedate', $criterias) ? $criterias['recipedate'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';

        return $this->em->getRepository("App\Entity\RecipeSubscription")->getRecipeSubscriptions($reference, $restaurant, $recipe, $recipeDate, $limit);
    }

    // Returns the bought subscriptions after applying the specified search criterias
    public function getOrderSubscriptions($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $reference = array_key_exists('reference', $criterias) ? $criterias['reference'] : 'all';
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $recipeDate = array_key_exists('recipedate', $criterias) ? $criterias['recipedate'] : 'all';
        $checkedin = array_key_exists('checkedin', $criterias) ? $criterias['checkedin'] : 'all';

        return $this->em->getRepository("App\Entity\OrderSubscription")->getOrderSubscriptions($reference, $keyword, $recipeDate, $checkedin);
    }

    // Returns the orders after applying the specified search criterias
    public function getOrders($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $status = array_key_exists('status', $criterias) ? $criterias['status'] : 1;
        $reference = array_key_exists('reference', $criterias) ? $criterias['reference'] : 'all';
        $user = array_key_exists('user', $criterias) ? $criterias['user'] : 'all';
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $recipe = array_key_exists('recipe', $criterias) ? $criterias['recipe'] : 'all';
        $recipeDate = array_key_exists('recipedate', $criterias) ? $criterias['recipedate'] : 'all';
        $recipeSubscription = array_key_exists('recipesubscription', $criterias) ? $criterias['recipesubscription'] : 'all';
        $upcomingSubscriptions = array_key_exists('upcomingsubscriptions', $criterias) ? $criterias['upcomingsubscriptions'] : 'all';
        $datefrom = array_key_exists('datefrom', $criterias) ? $criterias['datefrom'] : 'all';
        $dateto = array_key_exists('dateto', $criterias) ? $criterias['dateto'] : 'all';
        $paymentgateway = array_key_exists('paymentgateway', $criterias) ? $criterias['paymentgateway'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $ordersQuantityByDateStat = array_key_exists('ordersQuantityByDateStat', $criterias) ? $criterias['ordersQuantityByDateStat'] : false;
        $sumOrderElements = array_key_exists('sumOrderElements', $criterias) ? $criterias['sumOrderElements'] : false;

        if ($this->authChecker->isGranted(HasRoles::CREATOR) || $this->authChecker->isGranted(HasRoles::POINTOFSALE)) {
            /** @var User $user */
            $user = $this->security->getUser();
            $user = $user->getSlug();
        }

        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            /** @var User $user */
            $user = $this->security->getUser();
            $restaurant = $user->getRestaurant()?->getSlug();
        }

        return $this->em->getRepository("App\Entity\Order")->getOrders($status, $user, $restaurant, $recipe, $recipeDate, $recipeSubscription, $reference, $upcomingSubscriptions, $datefrom, $dateto, $paymentgateway, $sort, $order, $limit, $count, $ordersQuantityByDateStat, $sumOrderElements);
    }

    // Returns the users after applying the specified search criterias
    public function getUsers($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $role = array_key_exists('role', $criterias) ? $criterias['role'] : 'all';
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $createdbyrestaurantslug = array_key_exists('createdbyrestaurantslug', $criterias) ? $criterias['createdbyrestaurantslug'] : 'all';
        $restaurantname = array_key_exists('restaurantname', $criterias) ? $criterias['restaurantname'] : 'all';
        $restaurantslug = array_key_exists('restaurantslug', $criterias) ? $criterias['restaurantslug'] : 'all';
        $username = array_key_exists('username', $criterias) ? $criterias['username'] : 'all';
        $email = array_key_exists('email', $criterias) ? $criterias['email'] : 'all';
        $firstname = array_key_exists('firstname', $criterias) ? $criterias['firstname'] : 'all';
        $lastname = array_key_exists('lastname', $criterias) ? $criterias['lastname'] : 'all';
        $isVerified = array_key_exists('isVerified', $criterias) ? $criterias['isVerified'] : true;
        $isSuspended = array_key_exists('isSuspended', $criterias) ? $criterias['isSuspended'] : false;
        $countryslug = array_key_exists('countryslug', $criterias) ? $criterias['countryslug'] : 'all';
        $followedby = array_key_exists('followedby', $criterias) ? $criterias['followedby'] : 'all';
        $hasboughtsubscriptionforRecipe = array_key_exists('hasboughtsubscriptionfor', $criterias) ? $criterias['hasboughtsubscriptionfor'] : "all";
        $hasboughtsubscriptionforRestaurant = array_key_exists('hasboughtsubscriptionforrestaurant', $criterias) ? $criterias['hasboughtsubscriptionforrestaurant'] : "all";
        $apiKey = array_key_exists('apikey', $criterias) ? $criterias['apikey'] : "all";
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $isOnHomepageSlider = array_key_exists('isOnHomepageSlider', $criterias) ? $criterias['isOnHomepageSlider'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'u.createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;

        return $this->em->getRepository("App\Entity\User")->getUsers($role, $keyword, $createdbyrestaurantslug, $restaurantname, $restaurantslug, $username, $email, $firstname, $lastname, $isVerified, $isSuspended, $countryslug, $slug, $followedby, $hasboughtsubscriptionforRecipe, $hasboughtsubscriptionforRestaurant, $apiKey, $isOnHomepageSlider, $limit, $sort, $order, $count);
    }

    // Returns the testimonials after applying the specified search criterias
    public function getTestimonials($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $id = array_key_exists('id', $criterias) ? $criterias['id'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $user = array_key_exists('user', $criterias) ? $criterias['user'] : 'all';
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $rating = array_key_exists('rating', $criterias) ? $criterias['rating'] : 'all';
        $minrating = array_key_exists('minrating', $criterias) ? $criterias['minrating'] : 'all';
        $maxrating = array_key_exists('maxrating', $criterias) ? $criterias['maxrating'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';

        return $this->em->getRepository("App\Entity\Testimonial")->getTestimonials($keyword, $id, $slug, $user, $isOnline, $rating, $minrating, $maxrating, $limit, $count, $sort, $order);
    }

    // Returns the help center categories after applying the specified search criterias
    public function getHelpCenterCategories($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $parent = \array_key_exists('parent', $criterias) ? $criterias['parent'] : 'all';
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
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
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
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
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
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
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $order = \array_key_exists('order', $criterias) ? $criterias['order'] : 'c.name';
        $sort = \array_key_exists('sort', $criterias) ? $criterias['sort'] : 'ASC';

        return $this->em->getRepository("App\Entity\PostCategory")->getBlogPostCategories($isOnline, $keyword, $slug, $limit, $order, $sort);
    }

    // Returns the posts types after applying the specified search criterias
    public function getPostsTypes($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $isOnline = array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $keyword = array_key_exists('keyword', $criterias) ? $criterias['keyword'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'p.name';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';
        $hasvenues = array_key_exists('hasvenues', $criterias) ? $criterias['hasvenues'] : 'all';

        return $this->em->getRepository("App\Entity\PostType")->getPostsTypes($isOnline, $keyword, $slug, $limit, $sort, $order, $hasvenues);
    }

    // Removes all the specified user cart elements
    public function emptyCart(User $user): void
    {
        foreach ($user->getCartelements() as $cartelement) {
            $this->em->remove($cartelement);
        }
        $this->em->flush();
    }

    // Transforms the specified user cart into an order based on the application's logic
    public function transformCartIntoOrder(User $user): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setReference($this->generateReference(15));
        $order->setStatus(0);

        foreach ($user->getCartelements() as $cartelement) {
            // Creates as many order elements as cart elements
            $orderelement = new OrderElement();
            $orderelement->setOrder($order);
            $orderelement->setRecipeSubscription($cartelement->getRecipeSubscription());
            $orderelement->setUnitprice($cartelement->getRecipeSubscription()->getSalePrice());
            $orderelement->setQuantity($cartelement->getQuantity());
            $orderelement->setReservedSeats($cartelement->getReservedSeats());
            $order->addOrderelement($orderelement);
        }

        if ($user->hasRole(HasRoles::CREATOR)) {
            $order->setSubscriptionFee($this->getSettings('subscription_fee_online'));
            $order->setSubscriptionPricePercentageCut($this->getSettings('online_subscription_price_percentage_cut'));
        } elseif ($user->hasRole(HasRoles::POINTOFSALE)) {
            $order->setSubscriptionFee($this->getSettings('ticket_fee_pos'));
            $order->setSubscriptionPricePercentageCut($this->getSettings('pos_subscription_price_percentage_cut'));
        }

        $order->setCurrencyCcy($this->getSettings('currency_ccy'));
        $order->setCurrencySymbol($this->getSettings('currency_symbol'));

        return $order;
    }

    // Returns the payments after applying the specified search criterias
    public function getPayments($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $number = array_key_exists('number', $criterias) ? $criterias['number'] : 'all';

        return $this->em->getRepository("App\Entity\Payment")->getPayments($number);
    }

    // Returns the payout requests after applying the specified search criterias
    public function getPayoutRequests($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $reference = array_key_exists('reference', $criterias) ? $criterias['reference'] : 'all';
        $recipeDate = array_key_exists('recipedate', $criterias) ? $criterias['recipedate'] : 'all';
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        if ($this->authChecker->isGranted(HasRoles::RESTAURANT)) {
            /** @var User $user */
            $user = $this->security->getUser();
            $restaurant = $user->getRestaurant()->getSlug();
        }
        $datefrom = array_key_exists('datefrom', $criterias) ? $criterias['datefrom'] : 'all';
        $dateto = array_key_exists('dateto', $criterias) ? $criterias['dateto'] : 'all';
        $status = array_key_exists('status', $criterias) ? $criterias['status'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'createdAt';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'DESC';
        $limit = array_key_exists('limit', $criterias) ? $criterias['limit'] : 'all';
        $count = array_key_exists('count', $criterias) ? $criterias['count'] : false;

        return $this->em->getRepository("App\Entity\PayoutRequest")->getPayoutRequests($reference, $recipeDate, $restaurant, $datefrom, $dateto, $status, $sort, $order, $limit, $count);
    }

    // Returns the payment gateways after applying the specified search criterias
    public function getPaymentGateways($criterias): QueryBuilder
    {
        $this->disableSofDeleteFilterForAdmin($this->em, $this->authChecker);
        $restaurant = array_key_exists('restaurant', $criterias) ? $criterias['restaurant'] : 'all';
        $isOnline = \array_key_exists('isOnline', $criterias) ? $criterias['isOnline'] : true;
        $gatewayFactoryName = array_key_exists('gatewayFactoryName', $criterias) ? $criterias['gatewayFactoryName'] : 'all';
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'number';
        $order = array_key_exists('order', $criterias) ? $criterias['order'] : 'ASC';
        $restaurantPayoutPaypalEnabled = $this->getSettings('restaurant_payout_paypal_enabled');
        $restaurantPayoutStripeEnabled = $this->getSettings('restaurant_payout_stripe_enabled');

        return $this->em->getRepository("App\Entity\PaymentGateway")->getPaymentGateways($restaurant, $isOnline, $gatewayFactoryName, $slug, $sort, $order, $restaurantPayoutPaypalEnabled, $restaurantPayoutStripeEnabled);
    }

    // Sends the subscriptions to the creator
    public function sendOrderConfirmationEmail(Order $order, string $emailTo): int
    {
        $pdfOptions = new Options();
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);
        $html = $this->templating->render('dashboard/shared/order/subscription-pdf.html.twig', [
            'order' => $order,
            'recipeDateSubscriptionReference' => 'all'
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $subscriptionsPdfFile = $dompdf->output();

        $email = (new TemplatedEmail())
            ->to(new Address($emailTo))
            ->from(new Address(
                $this->getSettings('website_no_reply_email'),
                $this->getSettings('website_name')
            ))
            ->subject($this->translator->trans('Your subscriptions bought from') . ' ' . $this->getSettings('website_name'))
            ->htmlTemplate("dashboard/shared/order/confirmation-email.html.twig")
            ->context(['order' => $order])
            ->attach($subscriptionsPdfFile, $order->getReference() . "-" . $this->translator->trans("subscriptions") . '.pdf')
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $transport) {
            throw $transport;
        }

        return 1;
    }

    // Sends the payout processed email to the restaurant
    public function sendPayoutProcessedEmail($payoutRequest, string $emailTo): int
    {
        $email = (new TemplatedEmail())
            ->to(new Address($emailTo))
            ->from(new Address(
                $this->getSettings('website_no_reply_email'),
                $this->getSettings('website_name')
            ))
            ->subject($this->translator->trans('Your payout request has been processed'))
            ->htmlTemplate('dashboard/shared/payout/payout-processed-email.html.twig')
            ->context(['payoutRequest' => $payoutRequest])
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $transport) {
            throw $transport;
        }

        return 1;
    }

    // Handles all the operations needed after a successful payment processing
    public function handleSuccessfulPayment($orderReference): void
    {
        /** @var Order $order */
        $order = $this->getOrders(array('status' => 0, 'reference' => $orderReference))->getQuery()->getOneOrNullResult();
        $order->setStatus(1);
        $this->em->persist($order);
        $this->em->flush();

        foreach ($order->getOrderElements() as $orderelement) {
            for ($i = 0; $i <= $orderelement->getQuantity() - 1; $i++) {
                $subscription = new OrderSubscription();
                $subscription->setOrderElement($orderelement);
                $subscription->setReference($this->generateReference(20));
                $subscription->setIsScanned(false);
                if ($orderelement->getRecipeSubscription()->getRecipeDate()->getHasSeatingPlan()) {
                    $subscription->setReservedSeat($orderelement->getReservedSeats()[$i]);
                }
                $this->em->persist($subscription);
            }
            $this->em->flush();
        }

        foreach ($order->getUser()->getSubscriptionreservations() as $subscriptionReservation) {
            $this->em->remove($subscriptionReservation);
        }

        $this->em->flush();

        if ($order->getUser()->hasRole(HasRoles::CREATOR)) {
            $this->sendOrderConfirmationEmail($order, $order->getPayment()->getClientEmail());
        }
    }

    // Handles all the operations needed after a canceled payment processing
    public function handleCanceledPayment(string $orderReference, $note = null): void
    {
        /** @var Order $order */
        $order = $this->getOrders(['status' => 'all', 'reference' => $orderReference])->getQuery()->getOneOrNullResult();
        foreach ($order->getOrderelements() as $orderElement) {
            foreach ($orderElement->getSubscriptionReservations() as $subscriptionReservation) {
                $this->em->remove($subscriptionReservation);
                $this->em->flush();
            }
        }
        $order->setStatus(-1);
        $order->setNote($note);
        $this->em->persist($order);
        $this->em->flush();
    }

    // Handles all the operations needed after a failed payment processing
    public function handleFailedPayment(string $orderReference, $note = null): void
    {
        /** @var Order $order */
        $order = $this->getOrders(['status' => 0, 'reference' => $orderReference])->getQuery()->getOneOrNullResult();
        $order->setStatus(-2);
        $order->setNote($note);
        $this->em->persist($order);
        $this->em->flush();
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
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'l.name';
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
        $sort = array_key_exists('sort', $criterias) ? $criterias['sort'] : 'c.name';
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
        if ('categories_dropdown' == $link || 'footer_categories_section' == $link) {
            return $link;
        }
        if (false !== strpos($link, '/en/')) {
            return str_replace('/en/', '/'.$newLocale.'/', $link);
        } elseif (false !== strpos($link, '/fr/')) {
            return str_replace('/fr/', '/'.$newLocale.'/', $link);
        } elseif (false !== strpos($link, '/ar/')) {
            return str_replace('/ar/', '/'.$newLocale.'/', $link);
        } elseif (false !== strpos($link, '/es/')) {
            return str_replace('/es/', '/'.$newLocale.'/', $link);
        } elseif (false !== strpos($link, '/pt/')) {
            return str_replace('/pt/', '/'.$newLocale.'/', $link);
        } elseif (false !== strpos($link, '/de/')) {
            return str_replace('/de/', '/'.$newLocale.'/', $link);
        } elseif (false !== strpos($link, '/it/')) {
            return str_replace('/it/', '/'.$newLocale.'/', $link);
        } elseif (false !== strpos($link, '/br/')) {
            return str_replace('/br/', '/'.$newLocale.'/', $link);
        } elseif ($this->endsWith($link, '/en')) {
            return str_replace('/en', '/'.$newLocale, $link);
        } elseif ($this->endsWith($link, '/fr')) {
            return str_replace('/fr', '/'.$newLocale, $link);
        } elseif ($this->endsWith($link, '/ar')) {
            return str_replace('/ar', '/'.$newLocale, $link);
        } elseif ($this->endsWith($link, '/es')) {
            return str_replace('/es', '/'.$newLocale, $link);
        } elseif ($this->endsWith($link, '/pt')) {
            return str_replace('/pt', '/'.$newLocale, $link);
        } elseif ($this->endsWith($link, '/de')) {
            return str_replace('/de', '/'.$newLocale, $link);
        } elseif ($this->endsWith($link, '/it')) {
            return str_replace('/it', '/'.$newLocale, $link);
        } elseif ($this->endsWith($link, '/br')) {
            return str_replace('/br', '/'.$newLocale, $link);
        }

        return 'x';
    }

    // Returns an array containing the seats generated from a row config
    public function getRowSeats($row): array
    {
        $seats = [];
        if ('LTR' == $row['seatsNumbersDirection']) {
            for ($seatNumber = $row['seatsStartNumber']; $seatNumber <= $row['seatsEndNumber']; ++$seatNumber) {
                $seats[] = [
                    'label' => $row['label'].' '.$row['prefix'].' - '.$this->translator->trans('Seat').' '.$seatNumber,
                    'disabled' => in_array($seatNumber, $row['disabledSeats']),
                    'hidden' => in_array($seatNumber, $row['hiddenSeats']),
                    'number' => $seatNumber,
                ];
            }
        } else {
            for ($seatNumber = $row['seatsEndNumber']; $seatNumber >= $row['seatsStartNumber']; --$seatNumber) {
                $seats[] = [
                    'label' => $row['label'].' '.$row['prefix'].' - '.$this->translator->trans('Seat').' '.$seatNumber,
                    'disabled' => in_array($seatNumber, $row['disabledSeats']),
                    'hidden' => in_array($seatNumber, $row['hiddenSeats']),
                    'number' => $seatNumber,
                ];
            }
        }

        return $seats;
    }

    public function stringifySeatLabel($seatInfo): string
    {
        return $seatInfo['sectionName'].' - '.$seatInfo['rowLabel'].' '.$seatInfo['rowPrefix'].' - '.$this->translator->trans('Seat').' '.$seatInfo['seatNumber'];
    }

    // Returns the menus (header and footer)
    public function getMenus($criterias)
    {
        $slug = array_key_exists('slug', $criterias) ? $criterias['slug'] : 'all';
        $menus = $this->em->getRepository("App\Entity\Setting\Menu")->getMenus($slug);

        return $menus;
    }

    // Generates a list of pages to be chosen in a menu element
    public function getLinks(): array
    {
        $linksArray = [];

        // Add static pages urls
        $staticPages = $this->getPages([])->getQuery()->getResult();
        $staticPagesArray = [];
        $staticPagesArray[$this->translator->trans('Home')] = $this->router->generate('home', ['_locale' => 'en']);
        /** @var Page $staticPage */
        foreach ($staticPages as $staticPage) {
            $staticPagesArray[$staticPage->getTitle()] = $this->router->generate('page', ['slug' => $staticPage->getSlug(), '_locale' => 'en']);
        }
        $staticPagesArray[$this->translator->trans('Contact')] = $this->router->generate('contact', ['_locale' => 'en']);
        $linksArray[$this->translator->trans("Static Pages")] = $staticPagesArray;

        // Add Authentification pages urls
        $authentificationPagesArray = [];
        $authentificationPagesArray[$this->translator->trans('Login')] = $this->router->generate('login', ['_locale' => 'en']);
        $authentificationPagesArray[$this->translator->trans('Forgot Password')] = $this->router->generate('forgot_password_request', ['_locale' => 'en']);
        $authentificationPagesArray[$this->translator->trans('Creator Registration')] = $this->router->generate('register', ['_locale' => 'en']);
        $authentificationPagesArray[$this->translator->trans('Restauranter Registration')] = $this->router->generate('register_restaurant', ['_locale' => 'en']);
        $linksArray[$this->translator->trans("Authentification Pages")] = $authentificationPagesArray;

        // Add Dashboard pages urls
        $dashboardPagesArray = [];
        $dashboardPagesArray[$this->translator->trans('Creator subscriptions')] = $this->router->generate('dashboard_creator_orders', ['_locale' => 'en']);
        $dashboardPagesArray[$this->translator->trans('Create recipe')] = $this->router->generate('dashboard_restaurant_recipe_new', ['_locale' => 'en']);
        $linksArray[$this->translator->trans("Dashboard Pages")] = $dashboardPagesArray;

        // Add Category pages urls
        $categoryPagesArray = [];
        $categoryPagesArray[$this->translator->trans('Categories page')] = $this->router->generate('categories', ['_locale' => 'en']);
        $categoryPagesArray[$this->translator->trans('No link, display featured categories dropdown on hover (header menu only)')] = 'categories_dropdown';
        $categoryPagesArray[$this->translator->trans('Display top 4 featured categories (footer section menu only)')] = 'footer_categories_section';
        $categories = $this->getCategories([])->getQuery()->getResult();
        /** @var Category $category */
        foreach ($categories as $category) {
            $categoryPagesArray[$this->translator->trans('Category') . ' - ' . $category->getName()] = $this->router->generate('recipes', ['category' => $category->getSlug(), '_locale' => 'en']);
        }
        $linksArray[$this->translator->trans("Recipe Categories")] = $categoryPagesArray;

        // Add Post pages urls
        $postPagesArray = [];
        $postPagesArray[$this->translator->trans('Post page')] = $this->router->generate('post', ['_locale' => 'en']);
        $posts = $this->getBlogPosts([])->getQuery()->getResult();
        /** @var Post post */
        foreach ($posts as $post) {
            $postPagesArray[$this->translator->trans('Post') . ' - ' . $post->getTitle()] = $this->router->generate('post_article', ['slug' => $post->getSlug(), '_locale' => 'en']);
        }
        $linksArray[$this->translator->trans("Post Pages")] = $postPagesArray;

        // Add Recipe pages urls
        $recipePagesArray = [];
        $recipePagesArray[$this->translator->trans('Recipes page')] = $this->router->generate('recipes', ['_locale' => 'en']);
        $recipes = $this->getRecipes([])->getQuery()->getResult();
        /** @var Recipe $recipe */
        foreach ($recipes as $recipe) {
            $recipePagesArray[$this->translator->trans('Recipe') . ' - ' . $recipe->getTitle()] = $this->router->generate('recipe', ['slug' => $recipe->getSlug(), '_locale' => 'en']);
        }
        $linksArray[$this->translator->trans("Recipes Pages")] = $recipePagesArray;

        // Add Help center pages urls
        $helpCenterPagesArray = [];
        $helpCenterPagesArray[$this->translator->trans('Help Center page')] = $this->router->generate('help_center', ['_locale' => 'en']);
        $helpCenterCategories = $this->getHelpCenterCategories([])->getQuery()->getResult();
        $helpCenterArticles = $this->getHelpCenterArticles([])->getQuery()->getResult();
        /** @var HelpCenterCategory $helpCenterCategory */
        foreach ($helpCenterCategories as $helpCenterCategory) {
            $helpCenterPagesArray[$this->translator->trans('Help Center Category') . ' - ' . $helpCenterCategory->getName()] = $this->router->generate('help_center_category', ['slug' => $helpCenterCategory->getSlug(), '_locale' => 'en']);
        }
        /** @var HelpCenterArticle $helpCenterArticle */
        foreach ($helpCenterArticles as $helpCenterArticle) {
            $helpCenterPagesArray[$this->translator->trans('Help Center Article') . ' - ' . $helpCenterArticle->getTitle()] = $this->router->generate('help_center_article', ['slug' => $helpCenterArticle->getSlug(), '_locale' => 'en']);
        }
        $linksArray[$this->translator->trans("Help Center Pages")] = $helpCenterPagesArray;

        // Add Restaurants pages urls
        $restaurantsPagesArray = [];
        $restaurants = $this->getUsers(["role" => "restaurant"])->getQuery()->getResult();
        /** @var User $restaurant */
        foreach ($restaurants as $restaurant) {
            $restaurantsPagesArray[$this->translator->trans('Restaurant Profile') . ' - ' . $restaurant->getRestaurant()->getName()] = $this->router->generate('restaurant', ['slug' => $restaurant->getRestaurant()->getSlug(), '_locale' => 'en']);
        }
        $linksArray[$this->translator->trans("Restaurants Pages")] = $restaurantsPagesArray;

        // Add Venues pages urls
        $venuesPagesArray = [];
        $venuesPagesArray[$this->translator->trans('Venues page')] = $this->router->generate('venues', ['_locale' => 'en']);
        $venues = $this->getVenues([])->getQuery()->getResult();
        /** @var Venue $venue */
        foreach ($venues as $venue) {
            $venuesPagesArray[$this->translator->trans('Venue') . ' - ' . $venue->getName()] = $this->router->generate('venue', ['slug' => $venue->getSlug(), '_locale' => 'en']);
        }
        $linksArray[$this->translator->trans("Venues Pages")] = $venuesPagesArray;

        return $linksArray;
    }
}
