<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TestimonialFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photoFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => 'Image de profil',
                'translation_domain' => 'messages'
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Votre note (sur 5 étoiles)',
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => ['5 étoiles' => 5, '4 étoiles' => 4, '3 étoiles' => 3, '2 étoiles' => 2, '1 étoile' => 1],
            ])
            //->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            //'data_class' => Testimonial::class,
        ]);
    }
}
