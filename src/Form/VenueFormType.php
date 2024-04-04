<?php

namespace App\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\Amenity;
use App\Entity\Country;
use App\Entity\Venue;
use App\Entity\VenueType as Type;
use App\Service\SettingService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class VenueFormType extends AbstractType
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translations', TranslationsType::class, [
                'label' => 'Translation',
                'fields' => [
                    'name' => [
                        'purify_html' => true,
                        'locale_options' => [
                            'en' => ['label' => 'Name'],
                            'fr' => ['label' => 'Nom'],
                            'es' => ['label' => 'Nombre'],
                            'ar' => ['label' => 'اسم'],
                            'pt' => ['label' => 'Nome do menu'],
                            'de' => ['label' => 'Menüname'],
                            'it' => ['label' => 'Nome'],
                            'br' => ['label' => 'Nome'],
                        ],
                    ],
                    'description' => [
                        'field_type' => TextareaType::class,
                        'attr' => ['class' => 'wysiwyg'],
                        'locale_options' => [
                            'en' => ['label' => 'Description'],
                            'fr' => ['label' => 'Description'],
                            'es' => ['label' => 'Descripción'],
                            'ar' => ['label' => 'التفاصيل'],
                            'pt' => ['label' => 'Descrição'],
                            'de' => ['label' => 'Beschreibung'],
                            'it' => ['label' => 'Descrizione'],
                            'br' => ['label' => 'Descrição'],
                        ],
                    ],
                ],
                'excluded_fields' => ['slug'],
            ])
            ->add('type', EntityType::class, [
                'required' => true,
                'class' => Type::class,
                'choice_label' => 'name',
                'label' => t('Type :'),
                'query_builder' => function () {
                    return $this->settingService->getVenuesTypes([]);
                },
            ])
            ->add('amenities', EntityType::class, [
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'class' => Amenity::class,
                'choice_label' => 'name',
                'label' => t('Amenities :'),
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline'],
                'query_builder' => function () {
                    return $this->settingService->getAmenities([]);
                },
            ])
            ->add('seatedguests', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Seated guests number :'),
                'attr' => ['class' => 'touchspin-integer', 'data-max' => 100000],
            ])
            ->add('standingguests', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Standing guests number :'),
                'attr' => ['class' => 'touchspin-integer', 'data-max' => 100000],
            ])
            ->add('neighborhoods', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Neighborhoods :'),
            ])
            ->add('foodbeverage', TextareaType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Food and beverage details :'),
            ])
            ->add('pricing', TextareaType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Pricing'),
            ])
            ->add('availibility', TextareaType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Availibility :'),
            ])
            ->add('street', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Street address :'),
            ])
            ->add('street2', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Street address 2 :'),
            ])
            ->add('city', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('City :'),
            ])
            ->add('postalcode', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Zip / Postal code :'),
            ])
            ->add('state', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('State :'),
            ])
            ->add('country', EntityType::class, [
                'required' => true,
                'class' => Country::class,
                'choice_label' => 'name',
                'label' => t('Country :'),
                'attr' => ['class' => 'select2'],
                'placeholder' => t('Select an option'),
                'query_builder' => function () {
                    return $this->settingService->getCountries([]);
                },
            ])
            ->add('lat', HiddenType::class, [
                'required' => false,
            ])
            ->add('lng', HiddenType::class, [
                'required' => false,
            ])
            ->add('showmap', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the map along with the address on the venue page and recipe page :'),
                'choices' => ['No' => false, 'Yes' => true],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            ->add('quoteform', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the quote form on the venue page :'),
                'choices' => ['No' => false, 'Yes' => true],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            ->add('contactemail', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Contact email :'),
                'help' => t('This email address will be used to receive the quote requests, make sure to mention it if you want to show the quote form'),
            ])
            ->add('images', CollectionType::class, [
                'required' => false,
                'label' => t('Images :'),
                'entry_type' => VenueImageFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-collection',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Venue::class,
        ]);
    }
}
