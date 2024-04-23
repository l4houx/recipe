<?php

namespace App\Form;

use App\Entity\Pricing;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PricingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => t('Title :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
            ])
            ->add('subtitle', TextType::class, [
                'label' => t('Subtitle :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
            ])
            ->add('btn', ChoiceType::class, [
                'label' => t('Button Color :'),
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'multiple' => false,
                'expanded' => true,
                'empty_data' => '',
                'required' => true,
                'choices' => [
                    'Outline Dark Warning' => 'outline-dark-warning',
                    'Primary' => 'primary',
                    'Outline Dark Info' => 'outline-dark-info',
                ],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('btntitle', TextType::class, [
                'label' => t('Button title :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
            ])
            ->add('border', ChoiceType::class, [
                'label' => t('Border :'),
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'multiple' => false,
                'expanded' => true,
                'empty_data' => '',
                'required' => true,
                'choices' => [
                    'Dark Warning' => 'dark-warning',
                    'Dark Primary' => 'dark-primary',
                    'Dark Info' => 'dark-info',
                ],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('monthly', ChoiceType::class, [
                'label' => t('Monthly :'),
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'multiple' => false,
                'expanded' => true,
                'empty_data' => '',
                'required' => true,
                'choices' => [
                    'Monthly' => 'Monthly',
                    'Yearly' => 'Yearly',
                ],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('price', MoneyType::class, options: [
                'label' => t('Price :'),
                // 'divisor' => 100,
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new Positive(
                        message: t('The price cannot be negative')
                    ),
                ],
                'attr' => ['class' => 'touchspin-integer', 'data-min' => '1', 'data-max' => '1000000'],
            ])
            ->add('pricetitle', TextType::class, [
                'label' => t('Price title :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => false,
            ])
            ->add('duration', null, [
                'label' => t('Duration :'),
            ])
            ->add('stripeId', null, [
                'label' => t('Stripe Id :'),
                'empty_data' => '',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pricing::class,
        ]);
    }
}
