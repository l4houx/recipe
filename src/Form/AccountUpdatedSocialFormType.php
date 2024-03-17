<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use App\DTO\AccountUpdatedSocialDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use function Symfony\Component\Translation\t;

/**
 * @method AccountUpdatedSocialDTO getData()
 */
class AccountUpdatedSocialFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            // Social Media
            ->add('externallink', TextType::class, [
                'label' => t('External link :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => t("If you have a dedicated website, enter its URL here"),
                'attr' => ['placeholder' => t('External link URL'), 'class' => 'mb-1'],
            ])
            ->add('twitterurl', TextType::class, [
                'label' => t('Twitter profile URL :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => t("Add your Twitter profile URL."),
                'attr' => ['placeholder' => t('Twitter profile URL'), 'class' => 'mb-1'],
            ])
            ->add('instagramurl', TextType::class, [
                'label' => t('Instagram profile URL :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => t("Add your Instagram profile URL."),
                'attr' => ['placeholder' => t('Instagram profile URL'), 'class' => 'mb-1'],
            ])
            ->add('facebookurl', TextType::class, [
                'label' => t('Facebook profile URL :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => t("Add your Facebook profile URL."),
                'attr' => ['placeholder' => t('Facebook profile URL'), 'class' => 'mb-1'],
            ])
            ->add('googleplusurl', TextType::class, [
                'label' => t('Google Plus profile URL :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => t("Add your Google Plus profile URL."),
                'attr' => ['placeholder' => t('Google Plus profile URL'), 'class' => 'mb-1'],
            ])
            ->add('linkedinurl', TextType::class, [
                'label' => t('LinkedIn profile URL :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => t("Add your LinkedIn profile URL."),
                'attr' => ['placeholder' => t('LinkedIn profile URL'), 'class' => 'mb-1'],
            ])

            // Video
            ->add('youtubeurl', TextType::class, [
                'label' => t('Youtube profile URL :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => t("Add your YouTube profile URL."),
                'attr' => ['placeholder' => t('YouTube URL'), 'class' => 'mb-1'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', AccountUpdatedSocialDTO::class);
    }
}
