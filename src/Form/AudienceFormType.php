<?php

namespace App\Form;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use App\Entity\Audience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

use function Symfony\Component\Translation\t;

class AudienceFormType extends AbstractType
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
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Image'),
                'translation_domain' => 'messages',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Audience::class,
        ]);
    }
}
