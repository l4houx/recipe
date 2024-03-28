<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Recipe;
use App\Entity\Setting\HomepageHeroSetting;
use App\Entity\Setting\Menu;
use App\Entity\Setting\Setting;
use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Form\AppLayoutSettingFormType;
use App\Form\HomepageHeroSettingFormType;
use App\Service\SettingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

#[Route(path: '/%website_dashboard_path%/main-panel/manage-settings', name: 'dashboard_admin_setting_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class SettingController extends AdminBaseController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly SettingService $settingervice
    ) {
    }

    #[Route(path: '/layout', name: 'layout', methods: ['GET', 'POST'])]
    public function layout(Request $request): Response
    {
        $appLayoutSetting = $this->em->getRepository("App\Entity\AppLayoutSetting")->find(1);
        if (!$appLayoutSetting) {
            $this->addFlash('danger', $this->translator->trans('The layout settings could not be loaded'));

            return $this->redirectToRoute('dashboard_admin_setting_layout', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(AppLayoutSettingFormType::class, $appLayoutSetting)->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var Setting $setting */
            $setting = $request->request->all()['app_layout_settings'];

            if (!\array_key_exists('app_locales', (array) $setting)) {
                $form->get('app_locales')->addError(new \Symfony\Component\Form\FormError($this->translator->trans('You must specify at least one language')));
            } else {
                if (!\in_array($setting['default_locale'], $setting['app_locales'], true)) {
                    $form->get('default_locale')->addError(new \Symfony\Component\Form\FormError($this->translator->trans('The default locale must be selected in the available languages')));
                }
            }

            if ($form->isValid()) {
                $this->em->persist($appLayoutSetting);
                $this->em->flush();

                $this->settingervice->setSettings('maintenance_mode_custom_message', $setting['maintenance_mode_custom_message']);
                $this->settingervice->setSettings('date_format', $setting['date_format']);
                $this->settingervice->setSettings('date_format_simple', $setting['date_format_simple']);
                $this->settingervice->setSettings('date_format_date_only', $setting['date_format_date_only']);
                $this->settingervice->setSettings('date_timezone', $setting['date_timezone']);
                $this->settingervice->setSettings('date_timezone', $setting['date_timezone']);
                $this->settingervice->setSettings('default_locale', $setting['default_locale']);
                $this->settingervice->setSettings('app_locales', \in_array('app_locales', (array) $setting, true) ? $setting['app_locales'] : '');
                $this->settingervice->setSettings('website_name', $setting['website_name']);
                $this->settingervice->setSettings('website_slug', $setting['website_slug']);
                $this->settingervice->setSettings('website_url', $setting['website_url']);
                $this->settingervice->setSettings('website_root_url', $setting['website_root_url']);
                $this->settingervice->setSettings('website_dashboard_path', $setting['website_dashboard_path']);
                $this->settingervice->setSettings('website_description_en', $setting['website_description_en']);
                $this->settingervice->setSettings('website_description_fr', $setting['website_description_fr']);
                $this->settingervice->setSettings('website_description_es', $setting['website_description_es']);
                $this->settingervice->setSettings('website_description_ar', $setting['website_description_ar']);
                $this->settingervice->setSettings('website_description_de', $setting['website_description_de']);
                $this->settingervice->setSettings('website_description_pt', $setting['website_description_pt']);
                $this->settingervice->setSettings('website_keywords_en', $setting['website_keywords_en']);
                $this->settingervice->setSettings('website_keywords_fr', $setting['website_keywords_fr']);
                $this->settingervice->setSettings('website_keywords_es', $setting['website_keywords_es']);
                $this->settingervice->setSettings('website_keywords_ar', $setting['website_keywords_ar']);
                $this->settingervice->setSettings('website_keywords_de', $setting['website_keywords_de']);
                $this->settingervice->setSettings('website_keywords_pt', $setting['website_keywords_pt']);
                $this->settingervice->setSettings('website_contact_email', $setting['website_contact_email']);
                $this->settingervice->setSettings('website_sav', $setting['website_sav']);
                $this->settingervice->setSettings('website_no_reply_email', $setting['website_no_reply_email']);
                $this->settingervice->setSettings('website_contact_phone', $setting['website_contact_phone']);
                $this->settingervice->setSettings('website_contact_fax', $setting['website_contact_fax']);
                $this->settingervice->setSettings('website_contact_address', $setting['website_contact_address']);
                $this->settingervice->setSettings('facebook_url', $setting['facebook_url']);
                $this->settingervice->setSettings('instagram_url', $setting['instagram_url']);
                $this->settingervice->setSettings('youtube_url', $setting['youtube_url']);
                $this->settingervice->setSettings('twitter_url', $setting['twitter_url']);
                $this->settingervice->setSettings('app_layout', $setting['app_layout']);
                $this->settingervice->setSettings('app_theme', $setting['app_theme']);
                $this->settingervice->setSettings('primary_color', $setting['primary_color']);
                $this->settingervice->setSettings('custom_css', $setting['custom_css']);
                $this->settingervice->setSettings('footer_about', $setting['footer_about']);
                $this->settingervice->setSettings('google_analytics_code', $setting['google_analytics_code']);
                $this->settingervice->setSettings('users_can_register', $setting['users_can_register']);
                $this->settingervice->setSettings('show_back_to_top_button', $setting['show_back_to_top_button']);
                $this->settingervice->setSettings('show_terms_of_service_page', $setting['show_terms_of_service_page']);
                $this->settingervice->setSettings('terms_of_service_page_slug', $setting['terms_of_service_page_slug']);
                $this->settingervice->setSettings('show_privacy_policy_page', $setting['show_privacy_policy_page']);
                $this->settingervice->setSettings('privacy_policy_page_slug', $setting['privacy_policy_page_slug']);
                $this->settingervice->setSettings('show_cookie_policy_page', $setting['show_cookie_policy_page']);
                $this->settingervice->setSettings('cookie_policy_page_slug', $setting['cookie_policy_page_slug']);
                $this->settingervice->setSettings('show_cookie_policy_bar', $setting['show_cookie_policy_bar']);
                $this->settingervice->setSettings('show_gdpr_compliance_page', $setting['show_gdpr_compliance_page']);
                $this->settingervice->setSettings('gdpr_compliance_page_slug', $setting['gdpr_compliance_page_slug']);

                $this->settingervice->updateEnv('APP_ENV', $setting['app_environment']);
                $this->settingervice->updateEnv('APP_DEBUG', $setting['app_debug']);
                $this->settingervice->updateEnv('APP_SECRET', $setting['app_secret']);
                $this->settingervice->updateEnv('MAINTENANCE_MODE', $setting['maintenance_mode']);
                $this->settingervice->updateEnv('DATE_FORMAT', $setting['date_format']);
                $this->settingervice->updateEnv('DATE_FORMAT_SIMPLE', $setting['date_format_simple']);
                $this->settingervice->updateEnv('DATE_FORMAT_DATE_ONLY', $setting['date_format_date_only']);
                $this->settingervice->updateEnv('DATE_TIMEZONE', $setting['date_timezone']);
                $this->settingervice->updateEnv('DEFAULT_LOCALE', $setting['default_locale']);
                $this->settingervice->updateEnv('APP_LOCALES', implode('|', $setting['app_locales']).'|');

                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        } else {
            $form->get('app_environment')->setData($this->settingervice->getEnv('APP_ENV'));
            $form->get('app_debug')->setData($this->settingervice->getEnv('APP_DEBUG'));
            $form->get('app_secret')->setData($this->settingervice->getEnv('APP_SECRET'));
            $form->get('maintenance_mode')->setData($this->settingervice->getEnv('MAINTENANCE_MODE'));
            $form->get('date_format')->setData($this->settingervice->getEnv('DATE_FORMAT'));
            $form->get('date_format_simple')->setData($this->settingervice->getEnv('DATE_FORMAT_SIMPLE'));
            $form->get('date_format_date_only')->setData($this->settingervice->getEnv('DATE_FORMAT_DATE_ONLY'));
            $form->get('date_timezone')->setData($this->settingervice->getEnv('DATE_TIMEZONE'));
            $form->get('default_locale')->setData($this->settingervice->getEnv('DEFAULT_LOCALE'));
            $form->get('app_locales')->setData(array_filter(explode('|', $this->settingervice->getEnv('APP_LOCALES'))));
            $form->get('maintenance_mode_custom_message')->setData($this->settingervice->getSettings('maintenance_mode_custom_message'));
            $form->get('website_name')->setData($this->settingervice->getSettings('website_name'));
            $form->get('website_slug')->setData($this->settingervice->getSettings('website_slug'));
            $form->get('website_url')->setData($this->settingervice->getSettings('website_url'));
            $form->get('website_root_url')->setData($this->settingervice->getSettings('website_root_url'));
            $form->get('website_dashboard_path')->setData($this->settingervice->getSettings('website_dashboard_path'));
            $form->get('website_description_en')->setData($this->settingervice->getSettings('website_description_en'));
            $form->get('website_description_fr')->setData($this->settingervice->getSettings('website_description_fr'));
            $form->get('website_description_es')->setData($this->settingervice->getSettings('website_description_es'));
            $form->get('website_description_ar')->setData($this->settingervice->getSettings('website_description_ar'));
            $form->get('website_description_de')->setData($this->settingervice->getSettings('website_description_de'));
            $form->get('website_description_pt')->setData($this->settingervice->getSettings('website_description_pt'));
            $form->get('website_keywords_en')->setData($this->settingervice->getSettings('website_keywords_en'));
            $form->get('website_keywords_fr')->setData($this->settingervice->getSettings('website_keywords_fr'));
            $form->get('website_keywords_es')->setData($this->settingervice->getSettings('website_keywords_es'));
            $form->get('website_keywords_ar')->setData($this->settingervice->getSettings('website_keywords_ar'));
            $form->get('website_keywords_de')->setData($this->settingervice->getSettings('website_keywords_de'));
            $form->get('website_keywords_pt')->setData($this->settingervice->getSettings('website_keywords_pt'));
            $form->get('website_contact_email')->setData($this->settingervice->getSettings('website_contact_email'));
            $form->get('website_sav')->setData($this->settingervice->getSettings('website_sav'));
            $form->get('website_no_reply_email')->setData($this->settingervice->getSettings('website_no_reply_email'));
            $form->get('website_contact_phone')->setData($this->settingervice->getSettings('website_contact_phone'));
            $form->get('website_contact_fax')->setData($this->settingervice->getSettings('website_contact_fax'));
            $form->get('website_contact_address')->setData($this->settingervice->getSettings('website_contact_address'));
            $form->get('facebook_url')->setData($this->settingervice->getSettings('facebook_url'));
            $form->get('instagram_url')->setData($this->settingervice->getSettings('instagram_url'));
            $form->get('youtube_url')->setData($this->settingervice->getSettings('youtube_url'));
            $form->get('twitter_url')->setData($this->settingervice->getSettings('twitter_url'));
            $form->get('app_layout')->setData($this->settingervice->getSettings('app_layout'));
            $form->get('app_theme')->setData($this->settingervice->getSettings('app_theme'));
            $form->get('primary_color')->setData($this->settingervice->getSettings('primary_color'));
            $form->get('custom_css')->setData($this->settingervice->getSettings('custom_css'));
            $form->get('footer_about')->setData($this->settingervice->getSettings('footer_about'));
            $form->get('google_analytics_code')->setData($this->settingervice->getSettings('google_analytics_code'));
            $form->get('users_can_register')->setData($this->settingervice->getSettings('users_can_register'));
            $form->get('show_back_to_top_button')->setData($this->settingervice->getSettings('show_back_to_top_button'));
            $form->get('show_terms_of_service_page')->setData($this->settingervice->getSettings('show_terms_of_service_page'));
            $form->get('terms_of_service_page_slug')->setData($this->settingervice->getSettings('terms_of_service_page_slug'));
            $form->get('show_privacy_policy_page')->setData($this->settingervice->getSettings('show_privacy_policy_page'));
            $form->get('privacy_policy_page_slug')->setData($this->settingervice->getSettings('privacy_policy_page_slug'));
            $form->get('show_cookie_policy_page')->setData($this->settingervice->getSettings('show_cookie_policy_page'));
            $form->get('cookie_policy_page_slug')->setData($this->settingervice->getSettings('cookie_policy_page_slug'));
            $form->get('show_cookie_policy_bar')->setData($this->settingervice->getSettings('show_cookie_policy_bar'));
            $form->get('show_gdpr_compliance_page')->setData($this->settingervice->getSettings('show_gdpr_compliance_page'));
            $form->get('gdpr_compliance_page_slug')->setData($this->settingervice->getSettings('gdpr_compliance_page_slug'));
        }

        return $this->render('dashboard/admin/setting/layout.html.twig', compact('form'));
    }

    #[Route(path: '/blog', name: 'blog', methods: ['GET', 'POST'])]
    public function blog(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('blog_posts_per_page', TextType::class, [
                'required' => true,
                'label' => t('Number of blog posts per page'),
                'attr' => ['class' => 'touchspin-integer'],
            ])
            ->add('blog_comments_enabled', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Enable comments'),
                // 'choices' => ['No' => 'no', 'Native comments' => 'native', 'Facebook comments' => 'facebook', 'Disqus comments' => 'disqus'],
                'choices' => ['No' => 'no', 'Facebook comments' => 'facebook', 'Disqus comments' => 'disqus'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('facebook_app_id', TextType::class, [
                'required' => false,
                'label' => t('Facebook app id'),
                'help' => t('Go to the documentation to get help about getting an app ID'),
            ])
            ->add('disqus_subdomain', TextType::class, [
                'required' => false,
                'label' => t('Disqus subdomain'),
                'help' => t('Go to the documentation to get help about setting up Disqus'),
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm()
        ;
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Setting $setting */
                $setting = $form->getData();
                $this->settingervice->setSettings('blog_posts_per_page', $setting['blog_posts_per_page']);
                $this->settingervice->setSettings('blog_comments_enabled', $setting['blog_comments_enabled']);
                $this->settingervice->setSettings('facebook_app_id', $setting['facebook_app_id']);
                $this->settingervice->setSettings('disqus_subdomain', $setting['disqus_subdomain']);
                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        } else {
            $form->get('blog_posts_per_page')->setData($this->settingervice->getSettings('blog_posts_per_page'));
            $form->get('blog_comments_enabled')->setData($this->settingervice->getSettings('blog_comments_enabled'));
            $form->get('facebook_app_id')->setData($this->settingervice->getSettings('facebook_app_id'));
            $form->get('disqus_subdomain')->setData($this->settingervice->getSettings('disqus_subdomain'));
        }

        return $this->render('dashboard/admin/setting/blog.html.twig', compact('form', 'services'));
    }

    #[Route(path: '/google-recaptcha', name: 'google_recaptcha', methods: ['GET', 'POST'])]
    public function googleRecaptcha(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('google_recaptcha_enabled', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Enable Google Repatcha'),
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('google_recaptcha_site_key', TextType::class, [
                'required' => false,
                'label' => t('Site key'),
            ])
            ->add('google_recaptcha_secret_key', TextType::class, [
                'required' => false,
                'label' => t('Secret key'),
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm()
        ;
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Setting $setting */
                $setting = $form->getData();
                $this->settingervice->setSettings('google_recaptcha_enabled', $setting['google_recaptcha_enabled']);
                $this->settingervice->setSettings('google_recaptcha_site_key', $setting['google_recaptcha_site_key']);
                $this->settingervice->setSettings('google_recaptcha_secret_key', $setting['google_recaptcha_secret_key']);

                $this->settingervice->updateEnv('EWZ_RECAPTCHA_SITE_KEY', $setting['google_recaptcha_site_key']);
                $this->settingervice->updateEnv('EWZ_RECAPTCHA_SECRET', $setting['google_recaptcha_secret_key']);

                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        } else {
            $form->get('google_recaptcha_enabled')->setData($this->settingervice->getSettings('google_recaptcha_enabled'));
            $form->get('google_recaptcha_site_key')->setData($this->settingervice->getSettings('google_recaptcha_site_key'));
            $form->get('google_recaptcha_secret_key')->setData($this->settingervice->getSettings('google_recaptcha_secret_key'));
        }

        return $this->render('dashboard/admin/setting/google-recaptcha.html.twig', compact('form', 'services'));
    }

    #[Route(path: '/google-maps', name: 'google_maps', methods: ['GET', 'POST'])]
    public function googleMaps(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('google_maps_api_key', TextType::class, [
                'required' => false,
                'label' => t('Google Maps Api Key'),
                'help' => t('Leave api key empty to disable google maps project wide'),
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm()
        ;
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Setting $setting */
                $setting = $form->getData();
                $this->settingervice->updateEnv('GOOGLE_MAPS_API_KEY', $setting['google_maps_api_key']);

                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        } else {
            $form->get('google_maps_api_key')->setData($this->settingervice->getEnv('GOOGLE_MAPS_API_KEY'));
        }

        return $this->render('dashboard/admin/setting/google-maps.html.twig', compact('form'));
    }

    #[Route(path: '/mail-server', name: 'mail_server', methods: ['GET', 'POST'])]
    public function mailServer(Request $request): Response
    {
        if ('1' === $this->settingervice->getEnv('DEMO_MODE')) {
            $this->addFlash('error', $this->translator->trans('This feature is disabled in demo mode', [], 'javascript'));

            return $this->redirectToRoute('dashboard_main_panel', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createFormBuilder()
            ->add('mail_server_transport', ChoiceType::class, [
                'label' => t('Transport'),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => ['SMTP' => 'smtp', 'Gmail' => 'gmail', 'Sendmail' => 'sendmail'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('mail_server_host', TextType::class, [
                'label' => t('Host'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('mail_server_port', TextType::class, [
                'label' => t('Port'),
                'required' => false,
            ])
            ->add('mail_server_encryption', ChoiceType::class, [
                'label' => t('Encryption'),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => ['None' => null, 'SSL' => 'ssl', 'TLS' => 'tls'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            /* ->add('mail_server_auth_mode', ChoiceType::class, [
                'label' => t('Authentication mode'),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => ['None' => null, 'Login' => 'login', 'Cram-md5' => 'cram-md5', 'Plain' => 'plain'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                ])
            */
            ->add('mail_server_username', TextType::class, [
                'label' => t('Username'),
                'required' => false,
            ])
            ->add('mail_server_password', TextType::class, [
                'label' => t('Password'),
                'required' => false,
            ])
            ->add('no_reply_email', TextType::class, [
                'label' => t('No reply email address'),
                // 'purify_html' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('This email address will be used as the sender of all the emails sent by the platform, in almost all cases, it is the same as the username above'),
            ])
            ->add('contact_email', TextType::class, [
                'label' => t('Contact email'),
                // 'purify_html' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('This email address will receive the contact form messages'),
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Setting $setting */
                $setting = $form->getData();
                $this->settingervice->setSettings('mail_server_transport', $setting['mail_server_transport']);
                $this->settingervice->setSettings('mail_server_host', rawurlencode($setting['mail_server_host']));
                $this->settingervice->setSettings('mail_server_port', $setting['mail_server_port']);
                $this->settingervice->setSettings('mail_server_encryption', $setting['mail_server_encryption']);
                $this->settingervice->setSettings('mail_server_username', rawurlencode($setting['mail_server_username']));
                $this->settingervice->setSettings('mail_server_password', rawurlencode($setting['mail_server_password']));
                $this->settingervice->setSettings('website_contact_email', $setting['website_contact_email']);
                $this->settingervice->setSettings('website_no_reply_email', $setting['website_no_reply_email']);

                $dsnUrl = $setting['mail_server_transport'].'://';

                if (mb_strlen($setting['mail_server_username'])) {
                    $dsnUrl .= rawurlencode($setting['mail_server_username']);
                }
                if (mb_strlen($setting['mail_server_password'])) {
                    $dsnUrl .= ':'.rawurlencode($setting['mail_server_password']);
                }
                if (mb_strlen($setting['mail_server_host'])) {
                    $dsnUrl .= '@'.$setting['mail_server_host'];
                }
                if (mb_strlen($setting['mail_server_port'])) {
                    $dsnUrl .= ':'.$setting['mail_server_port'];
                }
                if (mb_strlen($setting['mail_server_encryption'])) {
                    $dsnUrl .= '/?encryption='.$setting['mail_server_encryption'];
                }
                if ('gmail' === $setting['mail_server_transport']) {
                    if (mb_strlen($setting['mail_server_encryption'])) {
                        $dsnUrl .= '&auth_mode=oauth';
                    } else {
                        $dsnUrl .= '?auth_mode=oauth';
                    }
                }
                $this->settingervice->updateEnv('MAILER_URL', $dsnUrl);

                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        } else {
            $form->get('mail_server_transport')->setData($this->settingervice->getSettings('mail_server_transport'));
            $form->get('mail_server_host')->setData(rawurldecode($this->settingervice->getSettings('mail_server_host')));
            $form->get('mail_server_port')->setData($this->settingervice->getSettings('mail_server_port'));
            $form->get('mail_server_encryption')->setData($this->settingervice->getSettings('mail_server_encryption'));
            $form->get('mail_server_username')->setData(rawurldecode($this->settingervice->getSettings('mail_server_username')));
            $form->get('mail_server_password')->setData(rawurldecode($this->settingervice->getSettings('mail_server_password')));
            $form->get('website_contact_email')->setData($this->settingervice->getSettings('website_contact_email'));
            $form->get('website_no_reply_email')->setData($this->settingervice->getSettings('website_no_reply_email'));
        }

        return $this->render('dashboard/admin/setting/mail-server.html.twig', compact('form'));
    }

    #[Route(path: '/mail-server/test', name: 'mail_server_test', methods: ['GET', 'POST'])]
    public function mailServerTest(Request $request, MailerInterface $mailer): Response
    {
        $email = (new TemplatedEmail())
            ->from(new Address(
                $this->settingervice->getSettings('website_no_reply_email'),
                $this->settingervice->getSettings('website_name'),
            ))
            ->to(new Address($request->query->get('email')))
            ->subject($this->translator->trans('Mail server test email'))
            ->htmlTemplate('dashboard/admin/setting/mail-server-test-email.html.twig')
            // ->context()
        ;

        try {
            $result = $mailer->send($email);
            if (0 === $result) {
                $this->addFlash('danger', $this->translator->trans('The email could not be sent'));
            } else {
                $this->addFlash('success', $this->translator->trans('The test email has been sent, please check the inbox of').' '.$request->request->get('email'));
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', $this->translator->trans('The email could not be sent'));
        }

        return $this->redirectToRoute('dashboard_admin_setting_mail_server_test', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/newsletter', name: 'newsletter', methods: ['GET', 'POST'])]
    public function newsletter(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('newsletter_enabled', ChoiceType::class, [
                'label' => t('Enable newsletter'),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('SSL must be activated on your hosting server in order to use Mailchimp'),
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('mailchimp_api_key', TextType::class, [
                'label' => t('Mailchimp app id'),
                'required' => false,
                'help' => t('Go to the documentation to get help about getting an api key'),
            ])
            ->add('mailchimp_list_id', TextType::class, [
                'label' => t('Mailchimp list id'),
                'required' => false,
                'help' => t('Go to the documentation to get help about getting a list id'),
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Setting $setting */
                $setting = $form->getData();
                $this->settingervice->setSettings('newsletter_enabled', $setting['newsletter_enabled']);
                $this->settingervice->setSettings('mailchimp_api_key', $setting['mailchimp_api_key']);
                $this->settingervice->setSettings('mailchimp_list_id', $setting['mailchimp_list_id']);
                $this->settingervice->updateEnv('NEWSLETTER_ENABLED', $setting['newsletter_enabled']);
                $this->settingervice->updateEnv('MAILCHIMP_API_KEY', $setting['mailchimp_api_key']);
                $this->settingervice->updateEnv('MAILCHIMP_LIST_ID', $setting['mailchimp_list_id']);
                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));
            } else {
                $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
            }
        } else {
            $form->get('newsletter_enabled')->setData($this->settingervice->getSettings('newsletter_enabled'));
            $form->get('mailchimp_api_key')->setData($this->settingervice->getSettings('mailchimp_api_key'));
            $form->get('mailchimp_list_id')->setData($this->settingervice->getSettings('mailchimp_list_id'));
        }

        return $this->render('dashboard/admin/setting/newsletter.html.twig', compact('form'));
    }

    /*
    #[Route(path: '/menus', name: 'menus', methods: ['GET'])]
    public function menus(Request $request): Response
    {
        /** @var Menu $menu /
        $menu = $this->settingervice->getMenus([])->getQuery()->getResult();

        return $this->render('dashboard/admin/setting/menus.html.twig', compact('menus'));
    }

    #[Route(path: '/menus/{slug}/edit', name: 'menus_edit', methods: ['GET', 'POST'])]
    public function menuEdit(Request $request, string $slug): Response
    {
        /** @var Menu $menu /
        $menu = $this->settingervice->getMenus(['slug' => $slug])->getQuery()->getOneOrNullResult();

        if (!$menu) {
            $this->addFlash('danger', $this->translator->trans('The menu can not be found'));

            return $this->redirectToRoute('dashboard_admin_setting_menus', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(MenuFormType::class, $menu)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                foreach ($menu->getMenuElements() as $menuElement) {
                    $menuElement->setMenu($menu);
                }
                $this->em->persist($menu);
                $this->em->flush();

                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

                return $this->redirectToRoute('dashboard_administrator_settings_menus');
            }
            $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
        }

        return $this->render('dashboard/admin/setting/menu-edit.html.twig', compact('form', 'menu'));
    }
    */

    #[Route(path: '/homepage-hero', name: 'homepage', methods: ['GET', 'POST'])]
    public function homepagehero(Request $request): Response
    {
        /** @var HomepageHeroSetting $homepageherosetting */
        $homepageherosetting = $this->em->getRepository("App\Entity\HomepageHeroSetting")->find(1);
        if (!$homepageherosetting) {
            $this->addFlash('error', $this->translator->trans('The homepage settings could not be loaded'));

            return $this->redirectToRoute('index', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(HomepageHeroSettingFormType::class, $homepageherosetting)->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $homeSliderRecipes = $this->settingervice->getRecipes(['isOnHomepageSlider' => true])->getQuery()->getResult();
                /** @var Recipe $recipe */
                foreach ($homeSliderRecipes as $recipe) {
                    $recipe->setIsonhomepageslider(null);
                    $this->em->persist($recipe);
                }
                $this->em->flush();
                foreach ($homepageherosetting->getRecipes() as $recipe) {
                    $recipe->setIsonhomepageslider($homepageherosetting);
                    $this->em->persist($recipe);
                }

                /** @var HomepageHeroSetting $homeSliderOwner */
                $homeSliderOwners = $this->settingervice->getUsers(['isOnHomepageSlider' => true, 'roles' => 'owner'])->getQuery()->getResult();
                /** @var User $user */
                foreach ($homeSliderOwners as $user) {
                    $user->setIsuseronhomepageslider(null);
                    $this->em->persist($user);
                }
                $this->em->flush();
                foreach ($homepageherosetting->getUsers() as $owner) {
                    $owner->setIsuseronhomepageslider($homepageherosetting);
                    $this->em->persist($owner);
                }

                $this->em->persist($homepageherosetting);
                $this->em->flush();

                $setting = $request->request->all()['homepage_hero_settings'];
                $this->settingervice->setSettings('homepage_show_search_box', $setting['homepage_show_search_box']);
                $this->settingervice->setSettings('homepage_ads_number', $setting['homepage_ads_number']);
                $this->settingervice->setSettings('homepage_categories_number', $setting['homepage_categories_number']);
                $this->settingervice->setSettings('homepage_posts_number', $setting['homepage_posts_number']);
                $this->settingervice->setSettings('homepage_show_call_to_action', $setting['homepage_show_call_to_action']);

                $this->addFlash('info', $this->translator->trans('Content was edited successfully.'));

                return $this->redirectToRoute('dashboard_admin_setting_homepage', [], Response::HTTP_SEE_OTHER);
            }
            $this->addFlash('danger', $this->translator->trans('The form contains invalid data'));
        } else {
            $form->get('homepage_show_search_box')->setData($this->settingervice->getSettings('homepage_show_search_box'));
            $form->get('homepage_ads_number')->setData($this->settingervice->getSettings('homepage_ads_number'));
            $form->get('homepage_categories_number')->setData($this->settingervice->getSettings('homepage_categories_number'));
            $form->get('homepage_posts_number')->setData($this->settingervice->getSettings('homepage_posts_number'));
            $form->get('homepage_show_call_to_action')->setData($this->settingervice->getSettings('homepage_show_call_to_action'));
        }

        return $this->render('dashboard/admin/setting/homepage.html.twig', compact('form'));
    }
}
