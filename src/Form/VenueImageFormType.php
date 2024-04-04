<?php

namespace App\Form;

use App\Entity\VenueImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

use function Symfony\Component\Translation\t;

class VenueImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Venue image :'),
                'translation_domain' => 'messages',
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'form-collection-position',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VenueImage::class,
        ]);
    }
}
