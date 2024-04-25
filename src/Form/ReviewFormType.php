<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class ReviewFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => t('Your rating (out of 5 stars) :'),
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => [t('5 stars') => 5, t('4 stars') => 4, t('3 stars') => 3, t('2 stars') => 2, t('1 star') => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            ->add('headline', TextType::class, [
                'label' => t('Title of your review :'),
                'purify_html' => true,
                'required' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => t("Tell the other participant more details about your experience :"),
                'purify_html' => true,
                'required' => true,
                'attr' => ['rows' => 10],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
