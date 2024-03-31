<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Testimonial;
use App\Form\Type\SwitchType;
use Symfony\Component\Form\FormEvents;
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
        parent::buildForm($builder, $options);

        $builder
            ->add('rating', ChoiceType::class, [
                'label' => t('Your rating (out of 5 stars) :'),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => ['5 stars' => 5, '4 stars' => 4, '3 stars' => 3, '2 stars' => 2, '1 star' => 1],
            ])
            ->add('content', TextareaType::class, [
                'label' => t("Content :"),
                'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
                'help' => t(''),
            ])
            ->add('author', UserAutocompleteField::class, ['label' => t('Author :')])
            ->add('isOnline', SwitchType::class, ['label' => t('Online')])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Testimonial::class,
        ]);
    }
}
