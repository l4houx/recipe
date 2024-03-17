<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Testimonial;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
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
                'label' => t('Profile picture'),
                'translation_domain' => 'messages'
            ])
            ->add('comment', TextareaType::class, [
                'label' => t('Comment :'),
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => t('Your rating (out of 5 stars)'),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => [t('5 stars') => 5, t('4 stars') => 4, t('3 stars') => 3, t('2 stars') => 2, t('1 star') => 1],
            ])
            //->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Testimonial::class,
        ]);
    }
}
