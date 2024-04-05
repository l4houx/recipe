<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Country;
use App\Entity\Restaurant;
use App\Service\SettingService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

use function Symfony\Component\Translation\t;

class RestaurantProfileFormType extends AbstractType
{
    public function __construct(private readonly SettingService $settingService)
    {
        // code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'purify_html' => true,
                'label' => t('Restaurant name :'),
            ])
            ->add('description', TextareaType::class, [
                'label' => t('About the restaurant :'),
                'required' => false,
                'purify_html' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
                'help' => t(''),
            ])
            ->add('categories', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => t('Categories :'),
                'help' => t('Select the categories that represent your recipes types'),
                'attr' => ['class' => 'select2'],
                'query_builder' => function () {
                    return $this->settingService->getCategories([]);
                },
            ])
            ->add('logoFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Restaurant logo :'),
                'translation_domain' => 'messages',
            ])
            ->add('coverFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Cover photo :'),
                'help' => t('Optionally add a cover photo to showcase your restaurant activities'),
                'translation_domain' => 'messages',
            ])
            ->add('country', EntityType::class, [
                'required' => false,
                'class' => Country::class,
                'choice_label' => 'name',
                'label' => t('Country :'),
                'attr' => ['class' => 'select2'],
                'query_builder' => function () {
                    return $this->settingService->getCountries([]);
                },
            ])
            ->add('website', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Website :'),
            ])
            ->add('email', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Email :'),
            ])
            ->add('phone', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Phone :'),
            ])
            ->add('facebook', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Facebook :'),
            ])
            ->add('twitter', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Twitter :'),
            ])
            ->add('instagram', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Instagram :'),
            ])
            ->add('googleplus', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Google Plus :'),
            ])
            ->add('linkedin', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('LinkedIn :'),
            ])
            ->add('youtubeurl', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Youtube video url :'),
                'help' => t('If you have an Youtube video that represents your activities as an recipe restaurant, add it in the standard format: https://www.youtube.com/watch?v=FzG4uDgje3M'),
            ])
            ->add('showvenuesmap', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show venues map :'),
                'choices' => ['Show' => true, 'Hide' => false],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Show a map at the bottom of your restaurant profile page containing the venues you added'),
            ])
            ->add('showfollowers', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show followers :'),
                'choices' => ['Show' => true, 'Hide' => false],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Show the number and list of people that follow you'),
            ])
            ->add('showreviews', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show reviews :'),
                'choices' => ['Show' => true, 'Hide' => false],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Show the reviews that you received for your recipes'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
