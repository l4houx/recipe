<?php

namespace App\DataFixtures;

use App\Entity\Setting\AppLayoutSetting;
use App\Entity\Setting\Currency;
use App\Entity\Setting\HomepageHeroSetting;
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
    public function __construct(protected readonly ParameterBagInterface $params)
    {
    }

    public function load(ObjectManager $manager)
    {
        // Meta
        $settings[] = new Setting('Description fr', 'website_description_fr', 'Achat et vente en ligne parmi des millions de produits en stock. Livraison gratuite à partir de 25€. Vos articles à petits prix : culture, high-tech, mode, jouets, sport, maison et bien plus !', TextareaType::class);
        $settings[] = new Setting('Keywords fr', 'website_keywords_fr', $this->params->get('website_name').', livres, achats en ligne, librairie, magazine, abonnement, musique, Cds, DVD, vidéo, électronique, jeux vidéo, ordinateurs, téléphones portables, jouets, jeux, vêtements, accessoires, chaussures, bijoux, montres, produits de bureau, sports en plein air, articles de sport, produits pour bébés, santé, soins personnels, maison, jardin, lit et de bain, meubles, outils, matériel, aspirateurs, vie en plein air, pièces pour véhicules automobiles, fournitures pour animaux de compagnie, à large bande, dsl', TextareaType::class);
        $settings[] = new Setting('Description en', 'website_description_en', 'Buy and sell online from millions of products in stock. Free delivery from 25€. Your articles at low prices: culture, high-tech, fashion, toys, sport, home and much more!', TextareaType::class);
        $settings[] = new Setting('Keywords en', 'website_keywords_en', $this->params->get('website_name').', books, online shopping, bookstore, magazine, subscription, music, Cds, DVD, video, electronics, video games, computers, cell phones, toys, games, clothing, accessories, shoes, jewelry, watches, office products, sports outdoors, sporting goods, baby products, health, personal care, home, garden, bed and bath, furniture, tools, equipment, vacuum cleaners, outdoor living, automotive parts, pet supplies, to broadband, dsl', TextareaType::class);

        // URLS and Name
        $settings[] = new Setting('Dashboard Path', 'website_dashboard_path', $this->params->get('website_dashboard_path'), TextType::class);
        $settings[] = new Setting('Site Name', 'header_name', $this->params->get('website_name'), TextType::class);
        $settings[] = new Setting('Site Name', 'website_name', $this->params->get('website_name'), TextType::class);
        $settings[] = new Setting('Site URL', 'website_url', $this->params->get('website_url'), UrlType::class);
        $settings[] = new Setting('Site slug', 'website_slug', $this->params->get('website_slug'), TextType::class);
        $settings[] = new Setting('Root URL', 'website_root_url', $this->params->get('website_root_url'), UrlType::class);
        $settings[] = new Setting('Website configured', 'is_website_configured', $this->params->get('is_website_configured'), CheckboxType::class);

        // Contact
        $settings[] = new Setting('No reply email', 'website_no_reply_email', $this->params->get('website_no_reply_email'), EmailType::class);
        $settings[] = new Setting('Response service email', 'website_sav', $this->params->get('website_sav'), EmailType::class);
        $settings[] = new Setting('Contact email', 'website_contact_email', $this->params->get('website_contact_email'), EmailType::class);
        $settings[] = new Setting('Contact by phone', 'website_contact_phone', $this->params->get('website_contact_phone'), TelType::class);
        $settings[] = new Setting('Contact by fax', 'website_contact_fax', $this->params->get('website_contact_fax'), TelType::class);
        $settings[] = new Setting('Contact address', 'website_contact_address', $this->params->get('website_contact_address'), TextareaType::class);
        $settings[] = new Setting('Name', 'name', $this->params->get('name'), TextType::class);
        $settings[] = new Setting('Support', 'website_support', $this->params->get('website_support'), EmailType::class);
        $settings[] = new Setting('Marketing', 'website_marketing', $this->params->get('website_marketing'), EmailType::class);
        $settings[] = new Setting('Compta', 'website_compta', $this->params->get('website_compta'), EmailType::class);

        // Company
        $settings[] = new Setting('Company', 'company', $this->params->get('company'), TextareaType::class);
        $settings[] = new Setting('Siret', 'siret', $this->params->get('siret'), TextareaType::class);
        $settings[] = new Setting('APE', 'ape', $this->params->get('ape'), TextareaType::class);
        $settings[] = new Setting('VAT', 'vat', $this->params->get('vat'), TextareaType::class);

        // Social
        $settings[] = new Setting('Facebook URL', 'facebook_url', 'https://www.facebook.com', UrlType::class);
        $settings[] = new Setting('Instagram URL', 'instagram_url', 'https://www.instagram.com', UrlType::class);
        $settings[] = new Setting('Youtube URL', 'youtube_url', 'https://www.youtube.com', UrlType::class);
        $settings[] = new Setting('Twitter URL', 'twitter_url', 'https://www.twitter.com', UrlType::class);

        // General settings
        $settings[] = new Setting('Copyright', 'website_copyright', '© 2020 '.$this->params->get('website_name').', Inc. All rights reserved.', TextType::class);
        $settings[] = new Setting('Everyone can sign up', 'users_can_register', true, CheckboxType::class);
        $settings[] = new Setting('About', 'website_about', 'Lorem ipsum dolor sit amet, consectetur adipisicing.', TextareaType::class);
        //$settings[] = new Setting('Primary color', 'primary_color', '#9a6ee2', TextType::class);
        $settings[] = new Setting('Back to top', 'show_back_to_top_button', true, CheckboxType::class);
        $settings[] = new Setting('Custom CSS', 'custom_css', '', TextareaType::class);
        $settings[] = new Setting('Google Analytics', 'google_analytics_code', '', TextareaType::class);
        $settings[] = new Setting('App Env', 'app_environment', 'dev', TextType::class);
        //$settings[] = new Setting('App Theme', 'app_theme', 'violet', TextType::class);
        //$settings[] = new Setting('App Layout', 'app_layout', 'container', TextType::class);
        $settings[] = new Setting('Maintenance mode', 'maintenance_mode', $this->params->get('maintenance_mode'), CheckboxType::class);
        $settings[] = new Setting('Custom maintenance mode message', 'maintenance_mode_custom_message', '', TextareaType::class);

        // Number
        $settings[] = new Setting('Number of posts search per page', 'website_posts_search_limit', 10, NumberType::class);
        $settings[] = new Setting('Number of recipes per page', 'website_recipes_limit', 5, NumberType::class);
        $settings[] = new Setting('Number of posts per page', 'website_posts_limit', 9, NumberType::class);
        $settings[] = new Setting('Number of comments per page', 'website_comments_limit', 4, NumberType::class);
        $settings[] = new Setting('Number of posts on the homepage', 'homepage_posts_number', 3, NumberType::class);
        $settings[] = new Setting('Number of testimonials on the homepage', 'homepage_testimonials_number', 5, NumberType::class);
        $settings[] = new Setting('Number of categories on the homepage', 'homepage_categories_number', 12, NumberType::class);
        $settings[] = new Setting('Number of recipes on the homepage', 'homepage_recipes_number', 12, NumberType::class);

        // Pages Show
        $settings[] = new Setting('Show Search Box On Home page', 'homepage_show_search_box', false, CheckboxType::class);
        $settings[] = new Setting('Show Call To Action', 'homepage_show_call_to_action', true, CheckboxType::class);
        $settings[] = new Setting('Show Cookie policy bar', 'show_cookie_policy_bar', true, CheckboxType::class);
        $settings[] = new Setting('Show Cookie policy page', 'show_cookie_policy_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Terms of Service page', 'show_terms_of_service_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Privacy policy page', 'show_privacy_policy_page', true, CheckboxType::class);
        $settings[] = new Setting('Show GDPR compliance page', 'show_gdpr_compliance_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Free Exchanges & Easy Returns page', 'show_free_exchanges_easy_returns_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Shipping page', 'show_shipping_page', true, CheckboxType::class);
        $settings[] = new Setting('Show About us page', 'show_about_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Affiliates page', 'show_affiliates_page', true, CheckboxType::class);
        $settings[] = new Setting('Show Careers page', 'show_careers_page', true, CheckboxType::class);

        // Pages Content
        $settings[] = new Setting('Cookie policy page content', 'cookie_policy_page_content', 'cookie_policy_page_content', TextareaType::class);
        $settings[] = new Setting('Terms of Service Page Content', 'terms_of_service_page_content', 'terms_of_service_page_content', TextareaType::class);
        $settings[] = new Setting('Privacy policy page content', 'privacy_policy_page_content', 'privacy_policy_page_content', TextareaType::class);
        $settings[] = new Setting('GDPR Compliance Page Content', 'gdpr_compliance_page_content', 'gdpr_compliance_page_content', TextareaType::class);
        $settings[] = new Setting('Free Exchanges & Easy Returns Page Content', 'free_exchanges_easy_returns_content', 'free_exchanges_easy_returns_page_content', TextareaType::class);
        $settings[] = new Setting('Free Shipping Page Content', 'shipping_content', 'shipping_page_content', TextareaType::class);
        $settings[] = new Setting('About us Page Content', 'about_content', 'about_page_content', TextareaType::class);
        $settings[] = new Setting('Affiliates Page Content', 'affiliates_content', 'affiliates_page_content', TextareaType::class);
        $settings[] = new Setting('Careers Page Content', 'careers_content', 'careers_page_content', TextareaType::class);

        // Pages Slug
        $settings[] = new Setting('Cookie policy page slug', 'cookie_policy_page_slug', 'cookie-policy', TextType::class);
        $settings[] = new Setting('Terms of Service Page Slug', 'terms_of_service_page_slug', 'terms-of-service', TextType::class);
        $settings[] = new Setting('Privacy Policy Page Slug', 'privacy_policy_page_slug', 'privacy-policy', TextType::class);
        $settings[] = new Setting('GDPR Compliance Page Slug', 'gdpr_compliance_page_slug', 'gdpr-compliance', TextType::class);
        $settings[] = new Setting('Free Exchanges & Easy Returns Page Slug', 'free_exchanges_easy_returns_page_slug', 'free-exchanges-easy-returns', TextType::class);
        $settings[] = new Setting('Shipping Page Slug', 'shipping_page_slug', 'shipping', TextType::class);
        $settings[] = new Setting('About us Page Slug', 'about_page_slug', 'about', TextType::class);
        $settings[] = new Setting('Affiliates Page Slug', 'affiliates_page_slug', 'affiliates', TextType::class);
        $settings[] = new Setting('Careers Page Slug', 'careers_page_slug', 'careers', TextType::class);

        // Newsletter
        $settings[] = new Setting('Show GDPR compliance page', 'mailchimp_api_key', '', TextType::class);
        $settings[] = new Setting('Show GDPR compliance page', 'mailchimp_list_id', '', TextType::class);
        $settings[] = new Setting('Newsletter enabled', 'newsletter_enabled', true, CheckboxType::class);

        // Currency
        $settings[] = new Setting('Currency to currency', 'currency_ccy', 'USD', TextType::class);
        $settings[] = new Setting('Currency symbol', 'currency_symbol', '$', TextType::class);
        $settings[] = new Setting('Currency position', 'currency_position', 'right', TextType::class);

        // Rss
        $settings[] = new Setting('Name', 'feed_name', 'Recipe RSS feed', TextType::class);
        $settings[] = new Setting('Description', 'feed_description', 'Latest recipes', TextareaType::class);
        $settings[] = new Setting('Limit', 'feed_recipes_limit', 100, NumberType::class);

        // Mail
        $settings[] = new Setting('Mail Server Transport', 'mail_server_transport', '', TextType::class);
        $settings[] = new Setting('Mail Server Host', 'mail_server_host', '', TextType::class);
        $settings[] = new Setting('Mail server port', 'mail_server_port', 'NULL', TextType::class);
        $settings[] = new Setting('Mail server encryption', 'mail_server_encryption', 'NULL', TextType::class);
        $settings[] = new Setting('Mail server authentication mode', 'mail_server_auth_mode', 'NULL', TextType::class);
        $settings[] = new Setting('Mail server username', 'mail_server_username', '', TextType::class);
        $settings[] = new Setting('Mail server password', 'mail_server_password', '', TextType::class);

        // Google
        $settings[] = new Setting('google recaptcha secret key', 'google_recaptcha_secret_key', '', TextType::class);
        $settings[] = new Setting('google recaptcha site key', 'google_recaptcha_site_key', '', TextType::class);
        $settings[] = new Setting('Google recaptcha enabled', 'google_recaptcha_enabled', false, CheckboxType::class);

        // Social login
        $settings[] = new Setting('Facebook secret key', 'social_login_facebook_secret', '', TextType::class);
        $settings[] = new Setting('Facebook login', 'social_login_facebook_id', '', TextType::class);
        $settings[] = new Setting('Facebook enabled', 'social_login_facebook_enabled', false, CheckboxType::class);
        $settings[] = new Setting('Google secret key', 'social_login_google_secret', '', TextType::class);
        $settings[] = new Setting('Google login', 'social_login_google_id', '', TextType::class);
        $settings[] = new Setting('Google enabled', 'social_login_google_enabled', false, CheckboxType::class);

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
            'CZK' => 'Kč',
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

        $manager->flush();
    }
}
