<?php

namespace App\DataFixtures;

use App\Entity\Setting\AppLayoutSetting;
use App\Entity\Setting\Currency;
use App\Entity\Setting\HomepageHeroSetting;
use App\Entity\Setting\Menu;
use App\Entity\Setting\MenuElement;
use App\Entity\Setting\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class AppSettingsFixtures extends Fixture
{
    use FakerTrait;

    private $counter = 1;

    public function __construct(protected readonly ParameterBagInterface $params)
    {
    }

    public function load(ObjectManager $manager)
    {
        // Meta FR
        $settings[] = new Setting('Description fr', 'website_description_fr', "Gestion des recettes et vente d'abonnements", TextareaType::class);
        $settings[] = new Setting('Keywords fr', 'website_keywords_fr', $this->params->get('website_name').', restaurant ma recette, abonnements en ligne, acheter des abonnements', TextareaType::class);

        // Meta EN
        $settings[] = new Setting('Description en', 'website_description_en', 'Recipe Management And Subscription Sales', TextareaType::class);
        $settings[] = new Setting('Keywords en', 'website_keywords_en', $this->params->get('website_name').', restaurant my recipe, subscriptions online, buy subscriptions', TextareaType::class);

        // Meta DE
        $settings[] = new Setting('Description de', 'website_description_de', 'Revenue Management und Abonnementverkauf', TextareaType::class);
        $settings[] = new Setting('Keywords de', 'website_keywords_de', $this->params->get('website_name').', Restaurant mein Rezept, Online-Abonnements, Abonnements kaufen', TextareaType::class);

        // Meta ES
        $settings[] = new Setting('Description es', 'website_description_es', 'Gestión de ingresos y venta de suscripciones', TextareaType::class);
        $settings[] = new Setting('Keywords es', 'website_keywords_es', $this->params->get('website_name').', restaurante mi receta, suscripciones online, comprar suscripciones', TextareaType::class);

        // URLS and Name
        $settings[] = new Setting('Dashboard Path', 'website_dashboard_path', $this->params->get('website_dashboard_path'), TextType::class);
        $settings[] = new Setting('Site Name', 'header_name', $this->params->get('website_name'), TextType::class);
        $settings[] = new Setting('Site Name', 'website_name', $this->params->get('website_name'), TextType::class);
        $settings[] = new Setting('Site URL', 'website_url', $this->params->get('website_url'), UrlType::class);
        $settings[] = new Setting('Site slug', 'website_slug', $this->params->get('website_slug'), TextType::class);
        $settings[] = new Setting('Root URL', 'website_root_url', $this->params->get('website_root_url'), UrlType::class);
        // $settings[] = new Setting('Website configured', 'is_website_configured', $this->params->get('is_website_configured'), CheckboxType::class);

        // Contact
        $settings[] = new Setting('No reply email', 'website_no_reply_email', $this->params->get('website_no_reply_email'), EmailType::class);
        $settings[] = new Setting('Response service email', 'website_sav', $this->params->get('website_sav'), EmailType::class);
        $settings[] = new Setting('Contact email', 'website_contact_email', $this->params->get('website_contact_email'), EmailType::class);
        $settings[] = new Setting('Contact by phone', 'website_contact_phone', $this->params->get('website_contact_phone'), TelType::class);
        $settings[] = new Setting('Contact by fax', 'website_contact_fax', $this->params->get('website_contact_fax'), TelType::class);
        $settings[] = new Setting('Contact address', 'website_contact_address', $this->params->get('website_contact_address'), TextareaType::class);
        $settings[] = new Setting('Contact Name', 'website_contact_name', $this->params->get('website_contact_name'), TextType::class);
        $settings[] = new Setting('Support', 'website_support', $this->params->get('website_support'), EmailType::class);
        $settings[] = new Setting('Marketing', 'website_marketing', $this->params->get('website_marketing'), EmailType::class);
        $settings[] = new Setting('Compta', 'website_compta', $this->params->get('website_compta'), EmailType::class);

        // Company
        $settings[] = new Setting('Company', 'website_company', $this->params->get('website_company'), TextareaType::class);
        $settings[] = new Setting('Siret', 'website_siret', $this->params->get('website_siret'), NumberType::class);
        $settings[] = new Setting('APE', 'website_ape', $this->params->get('website_ape'), TextType::class);
        $settings[] = new Setting('VAT', 'website_vat', $this->params->get('website_vat'), TextType::class);

        // Social
        $settings[] = new Setting('Facebook URL', 'website_facebook_url', 'https://www.facebook.com', UrlType::class);
        $settings[] = new Setting('Instagram URL', 'website_instagram_url', 'https://www.instagram.com', UrlType::class);
        $settings[] = new Setting('Youtube URL', 'website_youtube_url', 'https://www.youtube.com', UrlType::class);
        $settings[] = new Setting('Twitter URL', 'website_twitter_url', 'https://www.twitter.com', UrlType::class);

        // General settings
        $settings[] = new Setting('Copyright', 'website_copyright', '© 2020 '.$this->params->get('website_name').', Inc. All rights reserved.', TextType::class);
        $settings[] = new Setting('Everyone can sign up', 'users_can_register', true, CheckboxType::class);
        $settings[] = new Setting('About Footer', 'website_about', 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Dolore est repellendus adipisci voluptates, earum excepturi ut iusto quas alias, voluptatibus modi autem libero, ea delectus ex soluta quaerat aperiam atque!', TextareaType::class);
        $settings[] = new Setting('Primary color', 'primary_color', '#9a6ee2', TextType::class);
        $settings[] = new Setting('Back to top', 'show_back_to_top_button', true, CheckboxType::class);
        $settings[] = new Setting('Custom CSS', 'custom_css', '', TextareaType::class);
        $settings[] = new Setting('App Env', 'app_environment', 'dev', TextType::class);
        $settings[] = new Setting('App Theme', 'app_theme', 'dark', TextType::class);
        $settings[] = new Setting('App Layout', 'app_layout', 'container', TextType::class);
        $settings[] = new Setting('Maintenance mode', 'maintenance_mode', $this->params->get('maintenance_mode'), CheckboxType::class);
        $settings[] = new Setting('Custom maintenance mode message', 'maintenance_mode_custom_message', '', TextareaType::class);
        $settings[] = new Setting('Img Loader', 'img_loader', 'images/placeholders/img-404.jpg', TextareaType::class);

        // Limit
        $settings[] = new Setting('Limit Of Posts Search Per Page', 'website_posts_search_limit', 10, NumberType::class);

        // Limit Per Page
        $settings[] = new Setting('Limit Of Recipes Per Page', 'recipes_per_page', 9, NumberType::class);
        $settings[] = new Setting('Limit Of Posts Per Page', 'posts_per_page', 9, NumberType::class);
        $settings[] = new Setting('Limit Of Comments Per Page', 'comments_per_page', 4, NumberType::class);
        $settings[] = new Setting('Limit Of Reviews Per Page', 'reviews_per_page', 10, NumberType::class);

        // Number
        $settings[] = new Setting('Number Of Posts On The homepage', 'homepage_posts_number', 3, NumberType::class);
        $settings[] = new Setting('Number Of Testimonials On The homepage', 'homepage_testimonials_number', 2, NumberType::class);
        $settings[] = new Setting('Number Of Categories On The homepage', 'homepage_categories_number', 8, NumberType::class);
        $settings[] = new Setting('Number Of Recipes On The homepage', 'homepage_recipes_number', 12, NumberType::class);

        // Pages Show Action
        $settings[] = new Setting('Show Search Box On Home page', 'homepage_show_search_box', 0, CheckboxType::class);
        $settings[] = new Setting('Show Call To Action', 'homepage_show_call_to_action', true, CheckboxType::class);
        $settings[] = new Setting('Show Cookie policy bar', 'show_cookie_policy_bar', true, CheckboxType::class);

        // Pages Show
        $settings[] = new Setting('Show Cookie policy page', 'show_cookie_policy_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Terms of Service page', 'show_terms_of_service_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Privacy policy page', 'show_privacy_policy_page', true, CheckboxType::class);
        $settings[] = new Setting('Show GDPR compliance page', 'show_gdpr_compliance_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Free Exchanges & Easy Returns page', 'show_free_exchanges_easy_returns_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Shipping page', 'show_shipping_page', true, CheckboxType::class);
        $settings[] = new Setting('Show About us page', 'show_about_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Affiliates page', 'show_affiliates_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Feedbacks page', 'show_feedbacks_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Supports page', 'show_supports_page', true, CheckboxType::class);

        // Filter
        $settings[] = new Setting('Filter Category', 'show_category_filter', true, CheckboxType::class);
        $settings[] = new Setting('Filter Location', 'show_location_filter', true, CheckboxType::class);
        $settings[] = new Setting('Filter Date', 'show_date_filter', true, CheckboxType::class);
        $settings[] = new Setting('Filter Subscription', 'show_subscription_price_filter', true, CheckboxType::class);
        $settings[] = new Setting('Filter Audience', 'show_audience_filter', true, CheckboxType::class);

        // Button
        $settings[] = new Setting('Show Map', 'show_map_button', 0, CheckboxType::class);
        $settings[] = new Setting('Show Calendar', 'show_calendar_button', true, CheckboxType::class);
        $settings[] = new Setting('Show RSS Feed', 'show_rss_feed_button', true, CheckboxType::class);

        // Modal
        $settings[] = new Setting('Show Cart Modal', 'show_subscriptions_left_on_cart_modal', true, CheckboxType::class);

        // Restaurant
        $settings[] = new Setting('Payout Paypal', 'restaurant_payout_paypal_enabled', true, CheckboxType::class);
        $settings[] = new Setting('Payout Stripe', 'restaurant_payout_stripe_enabled', true, CheckboxType::class);
        $settings[] = new Setting('Subscription Fee Online', 'subscription_fee_online', '0.00', TextType::class);
        $settings[] = new Setting('Subscription Fee Pos', 'subscription_fee_pos', '0.00', TextType::class);
        $settings[] = new Setting('Pos Subscription Price', 'pos_subscription_price_percentage_cut', '0.00', TextType::class);
        $settings[] = new Setting('Online Subscription Price', 'online_subscription_price_percentage_cut', '0.00', TextType::class);
        $settings[] = new Setting('Checkout Timeleft', 'checkout_timeleft', '1800', TextType::class);

        // Pages Content
        $settings[] = new Setting('Cookie policy page content', 'cookie_policy_page_content', 'cookie_policy_page_content', TextareaType::class);
        $settings[] = new Setting('Terms of Service Page Content', 'terms_of_service_page_content', 'terms_of_service_page_content', TextareaType::class);
        $settings[] = new Setting('Privacy policy page content', 'privacy_policy_page_content', 'privacy_policy_page_content', TextareaType::class);
        $settings[] = new Setting('GDPR Compliance Page Content', 'gdpr_compliance_page_content', 'gdpr_compliance_page_content', TextareaType::class);
        $settings[] = new Setting('Free Exchanges & Easy Returns Page Content', 'free_exchanges_easy_returns_content', 'free_exchanges_easy_returns_page_content', TextareaType::class);
        $settings[] = new Setting('Free Shipping Page Content', 'shipping_content', 'shipping_page_content', TextareaType::class);
        $settings[] = new Setting('About us Page Content', 'about_content', 'about_page_content', TextareaType::class);
        $settings[] = new Setting('Affiliates Page Content', 'affiliates_content', 'affiliates_page_content', TextareaType::class);
        $settings[] = new Setting('Feedbacks Page Content', 'feedbacks_content', 'feedbacks_page_content', TextareaType::class);
        $settings[] = new Setting('Supports Page Content', 'supports_content', 'supports_page_content', TextareaType::class);

        // Pages Slug
        $settings[] = new Setting('Cookie policy page slug', 'cookie_policy_page_slug', 'cookie-policy', TextType::class);
        $settings[] = new Setting('Terms of Service Page Slug', 'terms_of_service_page_slug', 'terms-of-service', TextType::class);
        $settings[] = new Setting('Privacy Policy Page Slug', 'privacy_policy_page_slug', 'privacy-policy', TextType::class);
        $settings[] = new Setting('GDPR Compliance Page Slug', 'gdpr_compliance_page_slug', 'gdpr-compliance', TextType::class);
        $settings[] = new Setting('Free Exchanges & Easy Returns Page Slug', 'free_exchanges_easy_returns_page_slug', 'free-exchanges-easy-returns', TextType::class);
        $settings[] = new Setting('Shipping Page Slug', 'shipping_page_slug', 'shipping', TextType::class);
        $settings[] = new Setting('About us Page Slug', 'about_page_slug', 'about', TextType::class);
        $settings[] = new Setting('Affiliates Page Slug', 'affiliates_page_slug', 'affiliates', TextType::class);
        $settings[] = new Setting('Feedbacks Page Slug', 'feedback_page_slug', 'feedback', TextType::class);
        $settings[] = new Setting('Supports Page Slug', 'support_page_slug', 'support', TextType::class);

        // Newsletter
        $settings[] = new Setting('Mailchimp API Key', 'mailchimp_api_key', '', TextType::class);
        $settings[] = new Setting('Mailchimp List ID', 'mailchimp_list_id', '', TextType::class);
        $settings[] = new Setting('Newsletter Enabled', 'newsletter_enabled', true, CheckboxType::class);

        // Currency
        $settings[] = new Setting('Currency To Currency', 'currency_ccy', 'USD', TextType::class);
        $settings[] = new Setting('Currency Symbol', 'currency_symbol', '$', TextType::class);
        $settings[] = new Setting('Currency Position', 'currency_position', 'right', TextType::class);

        // Rss
        $settings[] = new Setting('Name', 'feed_name', 'Recipe RSS Feed', TextType::class);
        $settings[] = new Setting('Description', 'feed_description', 'Latest Recipes', TextareaType::class);
        $settings[] = new Setting('Limit', 'feed_recipes_limit', 100, NumberType::class);

        // Mail
        $settings[] = new Setting('Mail Server Transport', 'mail_server_transport', '', TextType::class);
        $settings[] = new Setting('Mail Server Host', 'mail_server_host', '', TextType::class);
        $settings[] = new Setting('Mail Server Port', 'mail_server_port', 'NULL', TextType::class);
        $settings[] = new Setting('Mail Server Encryption', 'mail_server_encryption', 'NULL', TextType::class);
        $settings[] = new Setting('Mail Server Authentication mode', 'mail_server_auth_mode', 'NULL', TextType::class);
        $settings[] = new Setting('Mail Server Username', 'mail_server_username', '', TextType::class);
        $settings[] = new Setting('Mail Server Password', 'mail_server_password', '', TextType::class);

        // Google
        $settings[] = new Setting('Google Recaptcha Secret Key', 'google_recaptcha_secret_key', '', TextType::class);
        $settings[] = new Setting('Google Recaptcha Site Key', 'google_recaptcha_site_key', '', TextType::class);
        $settings[] = new Setting('Google Recaptcha Enabled', 'google_recaptcha_enabled', 0, CheckboxType::class);
        $settings[] = new Setting('Google Map API Key', 'google_maps_api_key', '', TextType::class);
        $settings[] = new Setting('Google Analytics', 'google_analytics_code', '', TextareaType::class);

        // Social login Google
        $settings[] = new Setting('Google Secret Key', 'social_login_google_secret', '', TextType::class);
        $settings[] = new Setting('Google Login', 'social_login_google_id', '', TextType::class);
        $settings[] = new Setting('Google Enabled', 'social_login_google_enabled', 0, CheckboxType::class);

        // Social login Facebook
        $settings[] = new Setting('Facebook Secret Key', 'social_login_facebook_secret', '', TextType::class);
        $settings[] = new Setting('Facebook Login', 'social_login_facebook_id', '', TextType::class);
        $settings[] = new Setting('Facebook Enabled', 'social_login_facebook_enabled', 0, CheckboxType::class);

        // Comment
        $settings[] = new Setting('Venue Comments Enabled', 'venue_comments_enabled', 0, CheckboxType::class);
        $settings[] = new Setting('Post Comments Enabledd', 'post_comments_enabled', 0, CheckboxType::class);
        $settings[] = new Setting('Facebook Comments Enabled', 'facebook_app_id', '', TextType::class);
        $settings[] = new Setting('Disqus Comments Enabled', 'disqus_subdomain', '', TextType::class);

        foreach ($settings as $setting) {
            $manager->persist($setting);
        }

        // Hero Setting
        $homepages = [
            1 => [
                'title' => 'Discover Recipe',
                'paragraph' => 'Uncover the best recipes',
                'content' => 'custom',
                'custom_background_name' => 'homepage.jpg',
                'show_search_box' => 1,
            ],
            2 => [
                'title' => 'Discover Recipe',
                'paragraph' => 'Uncover the best recipes',
                'content' => 'recipes',
                'custom_background_name' => 'homepage.jpg',
                'show_search_box' => 1,
            ],
            3 => [
                'title' => 'Discover Restaurant',
                'paragraph' => 'Uncover the best restaurants',
                'content' => 'restaurants',
                'custom_background_name' => 'homepage.jpg',
                'show_search_box' => 1,
            ],
        ];

        foreach ($homepages as $key => $value) {
            $homepage = (new HomepageHeroSetting())
                ->setTitle($value['title'])
                ->setParagraph($value['paragraph'])
                ->setContent($value['content'])
                ->setCustomBackgroundName($value['custom_background_name'])
                ->setShowSearchBox((bool) $value['show_search_box'])
            ;

            $manager->persist($homepage);
        }

        // Layout Setting
        $layouts = [
            1 => [
                // Logo
                'logo_name' => '5f626cc22a186068458664.png',
                // Favicon
                'favicon_name' => '5ecac8821172a412596921.png',
                // OG
                'og_image_name' => '5faadc546e235285098877.jpg',
            ],
        ];

        foreach ($layouts as $key => $value) {
            $layout = (new AppLayoutSetting())
                // Logo
                ->setLogoName($value['logo_name'])
                // Favicon
                ->setFaviconName($value['favicon_name'])
                // OG
                ->setOgImageName($value['og_image_name'])
            ;

            $manager->persist($layout);
        }

        // Currency
        $currencies = [
            'AED' => 'د.إ',
            'AFN' => 'Af',
            'ALL' => 'L',
            'AMD' => 'Դ',
            'AOA' => 'Kz',
            'ARS' => '$',
            'AUD' => '$',
            'AWG' => 'ƒ',
            'AZN' => 'ман',
            'BAM' => 'КМ',
            'BBD' => '$',
            'BDT' => '৳',
            'BGN' => 'лв',
            'BHD' => 'ب.د',
            'BIF' => '₣',
            'BMD' => '$',
            'BND' => '$',
            'BOB' => 'Bs.',
            'BRL' => 'R$',
            'BSD' => '$',
            'BTN' => '',
            'BWP' => 'P',
            'BYN' => 'Br',
            'BZD' => '$',
            'CAD' => '$',
            'CDF' => '₣',
            'CHF' => '₣',
            'CLP' => '$',
            'CNY' => '¥',
            'COP' => '$',
            'CRC' => '₡',
            'CUP' => '$',
            'CVE' => '$',
            'CZK' => 'K�?',
            'DJF' => '₣',
            'DKK' => 'kr',
            'DOP' => '$',
            'DZD' => 'د.ج',
            'EGP' => '£',
            'ERN' => 'Nfk',
            'ETB' => '',
            'EUR' => '€',
            'FJD' => '$',
            'FKP' => '£',
            'GBP' => '£',
            'GEL' => 'ლ',
            'GHS' => '₵',
            'GIP' => '£',
            'GMD' => 'D',
            'GNF' => '₣',
            'GTQ' => 'Q',
            'GYD' => '$',
            'HKD' => '$',
            'HNL' => 'L',
            'HRK' => 'Kn',
            'HTG' => 'G',
            'HUF' => 'Ft',
            'IDR' => 'Rp',
            'ILS' => '₪',
            'INR' => '₹',
            'IQD' => 'ع.د',
            'IRR' => '﷼',
            'ISK' => 'Kr',
            'JMD' => '$',
            'JOD' => 'د.ا',
            'JPY' => '¥',
            'KES' => 'Sh',
            'KGS' => '',
            'KHR' => '៛',
            'KPW' => '₩',
            'KRW' => '₩',
            'KWD' => 'د.ك',
            'KYD' => '$',
            'KZT' => '〒',
            'LAK' => '₭',
            'LBP' => 'ل.ل',
            'LKR' => 'Rs',
            'LRD' => '$',
            'LSL' => 'L',
            'LYD' => 'ل.د',
            'MAD' => 'د.م.',
            'MDL' => 'L',
            'MGA' => '',
            'MKD' => 'ден',
            'MMK' => 'K',
            'MNT' => '₮',
            'MOP' => 'P',
            'MRU' => 'UM',
            'MUR' => '₨',
            'MVR' => 'ރ.',
            'MWK' => 'MK',
            'MXN' => '$',
            'MYR' => 'RM',
            'MZN' => 'MTn',
            'NAD' => '$',
            'NGN' => '₦',
            'NIO' => 'C$',
            'NOK' => 'kr',
            'NPR' => '₨',
            'NZD' => '$',
            'OMR' => 'ر.ع.',
            'PAB' => 'B/.',
            'PEN' => 'S/.',
            'PGK' => 'K',
            'PHP' => '₱',
            'PKR' => '₨',
            'PLN' => 'zł',
            'PYG' => '₲',
            'QAR' => 'ر.ق	',
            'RON' => 'L',
            'RSD' => 'din',
            'RUB' => 'р.',
            'RWF' => '₣',
            'SAR' => 'ر.س',
            'SBD' => '$',
            'SCR' => '₨',
            'SDG' => '£',
            'SEK' => 'kr',
            'SGD' => '$',
            'SHP' => '£',
            'SLL' => 'Le',
            'SOS' => 'Sh',
            'SRD' => '$',
            'STN' => 'Db',
            'SYP' => 'ل.س',
            'SZL' => 'L',
            'THB' => '฿',
            'TJS' => 'ЅМ',
            'TMT' => 'm',
            'TND' => 'د.ت',
            'TOP' => 'T$',
            'TRY' => '₤',
            'TTD' => '$',
            'TWD' => '$',
            'TZS' => 'Sh',
            'UAH' => '₴',
            'UGX' => 'Sh',
            'USD' => '$',
            'UYU' => '$',
            'UZS' => '',
            'VEF' => 'Bs F',
            'VND' => '₫',
            'VUV' => 'Vt',
            'WST' => 'T',
            'XAF' => '₣',
            'XCD' => '$',
            'XPF' => '₣',
            'YER' => '﷼',
            'ZAR' => 'R',
            'ZMW' => 'ZK',
            'ZWL' => '$',
        ];

        foreach ($currencies as $ccy => $symbol) {
            $currency = (new Currency())
                ->setCcy($ccy)
                ->setSymbol($symbol)
            ;

            $manager->persist($currency);
        }

        // Menu
        /** @var array<Menu> $menus */
        $menus = [
            1 => [
                'name' => 'Header menu',
                'slug' => 'header-menu',
                'header' => null,
            ],
            2 => [
                'name' => 'First Footer Section Menu',
                'slug' => 'first-footer-section-menu',
                'header' => 'Useful Links',
            ],
            3 => [
                'name' => 'Second Footer Section Menu',
                'slug' => 'second-footer-section-menu',
                'header' => 'My Account',
            ],
            4 => [
                'name' => 'Third Footer Section Menu',
                'slug' => 'third-footer-section-menu',
                'header' => 'Recipe Categories',
            ],
        ];

        foreach ($menus as $key => $value) {
            $menu = (new Menu())
                ->setName($value['name'])
                ->setSlug($value['slug'])
                ->setHeader($value['header'])
            ;
            $this->addReference('menu-'.$this->counter, $menu);
            ++$this->counter;

            $manager->persist($menu);
        }

        // Menu element
        /** @var array<MenuElement> $menuelements */
        $menuelements = [
            1 => [
                // 'menu_id' => 1,
                'label' => 'Home',
                'slug' => 'home',
                'icon' => 'fas fa-home',
                'link' => '/en',
                'custom_link' => null,
                'position' => 0,
            ],
            2 => [
                // 'menu_id' => 1,
                'label' => 'Browse Recipes',
                'slug' => 'browse-recipes',
                'icon' => 'fas fa-book',
                'link' => '/en/recipes',
                'custom_link' => null,
                'position' => 1,
            ],
            3 => [
                // 'menu_id' => 1,
                'label' => 'Explore',
                'slug' => 'explore',
                'icon' => 'fas fa-stream',
                'link' => 'categories_dropdown',
                'custom_link' => null,
                'position' => 2,
            ],
            4 => [
                // 'menu_id' => 1,
                'label' => 'Venues',
                'slug' => 'venues',
                'icon' => 'fas fa-compass',
                'link' => '/en/venues',
                'custom_link' => null,
                'position' => 3,
            ],
            5 => [
                // 'menu_id' => 1,
                'label' => 'How It works?',
                'slug' => 'how-it-works',
                'icon' => 'fas fa-question-circle',
                'link' => '/en/help-center',
                'custom_link' => null,
                'position' => 4,
            ],
            6 => [
                // 'menu_id' => 1,
                'label' => 'Blog',
                'slug' => 'blog',
                'icon' => 'fas fa-newspaper',
                'link' => '/en/blog',
                'custom_link' => null,
                'position' => 5,
            ],
            7 => [
                // 'menu_id' => 1,
                'label' => 'My subscriptions',
                'slug' => 'my-subscriptions',
                'icon' => 'fas fa-ticket-alt',
                'link' => '/en/dashboard/creator/my-subscriptions',
                'custom_link' => null,
                'position' => 6,
            ],
            8 => [
                // 'menu_id' => 1,
                'label' => 'Add my recipe',
                'slug' => 'add-my-recipe',
                'icon' => 'fas fa-calendar-plus',
                'link' => '/en/dashboard/restaurant/my-recipes/add',
                'custom_link' => null,
                'position' => 7,
            ],
            9 => [
                // 'menu_id' => 2,
                'label' => 'About us',
                'slug' => 'about-us',
                'icon' => null,
                'link' => '/en/page/about-us',
                'custom_link' => null,
                'position' => 0,
            ],
            10 => [
                // 'menu_id' => 2,
                'label' => 'Help center',
                'slug' => 'help-center',
                'icon' => null,
                'link' => '/en/help-center',
                'custom_link' => null,
                'position' => 1,
            ],
            11 => [
                // 'menu_id' => 2,
                'label' => 'Blog',
                'slug' => 'blog-2',
                'icon' => null,
                'link' => '/en/blog',
                'custom_link' => null,
                'position' => 2,
            ],
            12 => [
                // 'menu_id' => 2,
                'label' => 'Venues',
                'slug' => 'venues-1',
                'icon' => null,
                'link' => '/en/venues',
                'custom_link' => null,
                'position' => 3,
            ],
            13 => [
                // 'menu_id' => 2,
                'label' => 'Send us an email',
                'slug' => 'send-us-an-email',
                'icon' => null,
                'link' => '/en/contact',
                'custom_link' => null,
                'position' => 4,
            ],
            14 => [
                // 'menu_id' => 3,
                'label' => 'Create an account',
                'slug' => 'create-an-account',
                'icon' => null,
                'link' => '/en/signup',
                'custom_link' => null,
                'position' => 0,
            ],
            15 => [
                // 'menu_id' => 3,
                'label' => 'Create an account',
                'slug' => 'create-an-account',
                'icon' => null,
                'link' => '/en/signup/restaurant',
                'custom_link' => null,
                'position' => 1,
            ],
            16 => [
                // 'menu_id' => 3,
                'label' => 'Sell subscriptions online',
                'slug' => 'sell-subscriptions-online',
                'icon' => null,
                'link' => '/en/dashboard/creator/my-subscriptions',
                'custom_link' => null,
                'position' => 2,
            ],
            17 => [
                // 'menu_id' => 3,
                'label' => 'Forgot your password ?',
                'slug' => 'forgot-your-password',
                'icon' => null,
                'link' => '/en/reset-password',
                'custom_link' => null,
                'position' => 3,
            ],
            18 => [
                // 'menu_id' => 3,
                'label' => 'Pricing and fees',
                'slug' => 'pricing-and-fees',
                'icon' => null,
                'link' => '/en/page/pricing-and-fees',
                'custom_link' => null,
                'position' => 4,
            ],
            19 => [
                // 'menu_id' => 4,
                'label' => 'All categories',
                'slug' => 'all-categories',
                'icon' => null,
                'link' => 'footer_categories_section',
                'custom_link' => null,
                'position' => 0,
            ],
            20 => [
                // 'menu_id' => 4,
                'label' => 'All categories',
                'slug' => 'all-categories',
                'icon' => null,
                'link' => '/en/categories',
                'custom_link' => null,
                'position' => 1,
            ],
        ];

        foreach ($menuelements as $key => $value) {
            $menuelement = (new MenuElement())
                ->setId($key)
                ->setMenu($this->getReference('menu-'.$this->faker()->numberBetween(1, 4)))
                ->setLabel($value['label'])
                ->setSlug($value['slug'])
                ->setIcon($value['icon'])
                ->setLink($value['link'])
                ->setCustomLink($value['custom_link'])
                ->setPosition($value['position'])
            ;

            $manager->persist($menuelement);
        }

        $manager->flush();
    }
}
