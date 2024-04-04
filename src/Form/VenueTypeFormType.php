<?php

namespace App\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\VenueType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class VenueTypeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('translations', TranslationsType::class, [
                'label' => t('Translation :'),
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
                ],
                'excluded_fields' => ['slug'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VenueType::class,
        ]);
    }
}
