<?php

namespace App\Form;

use App\Entity\PaymentGateway;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class RestaurantPayoutPaymentGatewayFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('config', PaymentGatewayConfigFormType::class, [
                'label' => false,
                'auto_initialize' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentGateway::class,
        ]);
    }
}
