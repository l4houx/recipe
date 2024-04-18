<?php

namespace App\Form;

use App\Entity\Setting\Page;
use App\Service\SettingService;
use App\Entity\Setting\AppLayoutSetting;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppLayoutSettingFormType extends AbstractType
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly ParameterBagInterface $params
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $pages = [];

        $availableLocales = array_values(array_filter(explode('|', $this->params->get('app_locales'))));
        $availableLocales = array_combine(array_values($availableLocales), array_values($availableLocales));

        /** @var Page $page */
        foreach ($this->settingService->getPages([])->getQuery()->getResult() as $page) {
            $pages[$page->getTitle()] = $page->getSlug();
        }

        $builder
            ->add('app_environment', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('App Environment'),
                'choices' => ['Production' => 'prod', 'Development' => 'dev'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Development environment is used for development purposes only'),
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('app_debug', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('App Debugging'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Enable to display stacktraces on error pages or if cache files should be dynamically rebuilt on each request'),
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('app_secret', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('App Secret'),
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('This is a string that should be unique to your application and it is commonly used to add more entropy to security related operations'),
            ])
            ->add('maintenance_mode', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Maintenance mode'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Enable maintenance mode to display a maintenance page for all users but the users who are granted the ROLE_ADMIN_APPLICATION role, if you lost your session, you can edit the MAINTENANCE_MODE parameter directly in the .env file'),
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('maintenance_mode_custom_message', TextareaType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Maintenance mode custom message'),
            ])
            ->add('date_format', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Date and time format'),
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('Project wide date and time format, follow this link for a list of supported characters: https://unicode-org.github.io/icu/userguide/format_parse/datetime/ . Please make sure to keep the double quotes " " around the format string'),
            ])
            ->add('date_format_simple', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Alternative date and time format'),
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('Used in some specific cases, follow this link for a list of supported characters: https://www.php.net/manual/en/datetime.format.php . Please make sure to keep the double quotes " " around the format string'),
            ])
            ->add('date_format_date_only', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Date only format'),
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('Used in some specific cases, follow this link for a list of supported characters: https://www.php.net/manual/en/datetime.format.php . Please make sure to keep the double quotes " " around the format string'),
            ])
            ->add('date_timezone', TimezoneType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'label' => t('Timezone'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('default_locale', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => $availableLocales,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'label' => t('Default language'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('app_locales', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'choices' => $availableLocales,
                'label' => t('Available languages'),
                'constraints' => [
                    new Count(['min' => 0]),
                ],
            ])
            ->add('website_name', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Website name'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_slug', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Website slug'),
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('Enter the chosen website name with no spaces and no uppercase characters (for SEO purposes)'),
            ])
            ->add('website_url', UrlType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Website url'),
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t('Enter the full website url'),
            ])
            ->add('website_root_url', UrlType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Website root url'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_dashboard_path', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Website dashboard path'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_description_en', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_description_fr', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_description_es', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_description_ar', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_description_de', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_description_pt', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_keywords_en', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('SEO keywords'),
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_keywords_fr', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_keywords_es', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_keywords_ar', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_keywords_de', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_keywords_pt', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('website_contact_email', EmailType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Contact email'),
            ])
            ->add('website_sav', EmailType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Contact sav'),
            ])
            ->add('website_no_reply_email', EmailType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Contact no reply email'),
            ])
            ->add('website_contact_phone', TelType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Contact phone'),
            ])
            ->add('website_contact_fax', TelType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Contact fax'),
            ])
            ->add('website_contact_address', TextareaType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Contact address'),
            ])
            ->add('website_contact_name', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Contact name'),
            ])
            ->add('website_company', TextareaType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Company'),
            ])
            ->add('website_siret', NumberType::class, [
                'mapped' => false,
                'required' => false,
                'label' => t('Company siret'),
            ])
            ->add('website_ape', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Company ape'),
            ])
            ->add('website_vat', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Company vat'),
            ])
            ->add('facebook_url', UrlType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Facebook url'),
            ])
            ->add('instagram_url', UrlType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Instagram url'),
            ])
            ->add('youtube_url', UrlType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Youtube url'),
            ])
            ->add('twitter_url', UrlType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Twitter url'),
            ])
            ->add('app_layout', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Application layout'),
                'choices' => ['Compact' => 'container', 'Fluid' => 'container-fluid'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('app_theme', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Application theme'),
                'choices' => ['Orange' => 'orange', 'Light blue' => 'lightblue', 'Dark blue' => 'darkblue',
                    'Yellow' => 'yellow', 'Purple' => 'purple', 'Pink' => 'pink', 'Red' => 'red', 'Green' => 'green', 'Dark' => 'dark'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('primary_color', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Primary color code'),
                'attr' => ['readonly' => true],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('logoFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Logo'),
                'help' => t('Please choose a 200x50 image size to ensure compatibility with the website design'),
                'translation_domain' => 'messages',
            ])
            ->add('faviconFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Favicon'),
                'help' => t('We recommend a 48x48 image size'),
                'translation_domain' => 'messages',
            ])
            ->add('ogImageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Social media share image'),
                'help' => t('Please choose a 200x200 minimum image size as it is required by Facebook'),
                'translation_domain' => 'messages',
            ])
            ->add('show_back_to_top_button', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the back to top button'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('show_terms_of_service_page', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the terms of service page link'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('terms_of_service_page_slug', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'label' => t('Terms of service page slug'),
                'choices' => $pages,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('show_privacy_policy_page', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the privacy policy page link'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('privacy_policy_page_slug', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'label' => t('Privacy policy page slug'),
                'choices' => $pages,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('show_cookie_policy_page', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the cookie policy page link'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('cookie_policy_page_slug', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'label' => t('Cookie policy page slug'),
                'choices' => $pages,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('show_cookie_policy_bar', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the cookie policy bar at the bottom'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('show_gdpr_compliance_page', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the GDPR compliance page link'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('gdpr_compliance_page_slug', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'label' => t('Gdpr compliance page slug'),
                'choices' => $pages,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('show_feedback_page', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the Feedback page link'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('feedback_page_slug', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'label' => t('Feedback page slug'),
                'choices' => $pages,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('show_support_page', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the Support page link'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('support_page_slug', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'label' => t('Support page slug'),
                'choices' => $pages,
                'attr' => ['class' => 'select2', 'data-sort-options' => '1'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('custom_css', TextareaType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Custom css'),
                'attr' => ['rows' => '15'],
            ])
            ->add('website_about', TextareaType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('About footer'),
                'attr' => ['rows' => '15'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('google_analytics_code', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => false,
                'label' => t('Google analytics Tracking ID'),
                'help' => t('e.g. UA-000000-2'),
            ])
            ->add('users_can_register', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Everyone can register'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AppLayoutSetting::class,
            'validation_groups' => ['create', 'update'],
        ]);
    }
}
