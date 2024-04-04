<?php

namespace App\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\Amenity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class AmenityFormType extends AbstractType
{
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
                            'pt' => ['label' => 'Nome'],
                            'de' => ['label' => 'Name'],
                            'it' => ['label' => 'Nome'],
                            'br' => ['label' => 'Nome'],
                        ],
                    ],
                ],
                'excluded_fields' => ['slug'],
            ])
            ->add('icon', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Icon :'),
                'attr' => ['class' => 'icon-picker', 'autocomplete' => 'disabled'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Amenity::class,
        ]);
    }
}
