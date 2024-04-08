<?php

namespace App\Form;

use App\Entity\Pricing;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
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
            /*
            ->add('ImageFile', VichFileType::class, [
                'label' => t('Image :'),
            ])
            */
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Main pricing image :'),
                'translation_domain' => 'messages'
            ])
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
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'choices' => [
                    'Primary' => 'primary',
                    'Outline buttons (Primary)' => 'outline-primary',
                    'Secondary' => 'secondary',
                    'Outline buttons (Secondary)' => 'outline-secondary',
                    'Success' => 'success',
                    'Outline buttons (Success)' => 'outline-success',
                    'Danger' => 'danger',
                    'Outline buttons (Danger)' => 'outline-danger',
                    'Warning' => 'warning',
                    'Outline buttons (Warning)' => 'outline-warning',
                    'Info' => 'info',
                    'Outline buttons (Info)' => 'outline-info',
                ],
            ])
            ->add('monthly', ChoiceType::class, [
                'label' => t('Monthly :'),
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'choices' => [
                    'Monthly' => 'Monthly',
                    'Yearly' => 'Yearly',
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
            ])
            ->add('duration', null, [
                'label' => t('Duration :'),
            ])
            ->add('symbol', ChoiceType::class, [
                'label' => t('Currency symbol :'),
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'choices' => [
                    'Euro' => '€',
                    'Dollar' => '$',
                ],
            ])
            ->add('stripeId', TextType::class, [
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
