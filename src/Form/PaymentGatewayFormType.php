<?php

namespace App\Form;

use App\Entity\PaymentGateway;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

use function Symfony\Component\Translation\t;

class PaymentGatewayFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 30]),
                ],
            ])
            ->add('factoryName', ChoiceType::class, [
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'label' => t('Choose a payment gateway'),
                'choices' => [
                    // 'Authorize.Net AIM' => 'authorize_net_aim',
                    // 'Be2Bill Direct' => 'be2bill_direct',
                    // 'Be2Bill Offsite' => 'be2bill_offsite',
                    // 'Klarna Checkout' => 'klarna_checkout',
                    // 'Klarna Invoice' => 'klarna_invoice',
                    'Cash / Check / Bank Transfer / Other' => 'offline',
                    // 'Payex' => 'payex',
                    'Paypal Express Checkout' => 'paypal_express_checkout',
                    // 'Paypal Rest' => 'paypal_rest',
                    // 'Paypal Pro Checkout' => 'paypal_pro_checkout',
                    // 'Sofort' => 'sofort',
                    // 'Stripe.js' => 'stripe_js',
                    'Stripe Checkout (credit cards)' => 'stripe_checkout',
                    'Flutterwave' => 'flutterwave',
                    'Mercado Pago' => 'mercadopago',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'help' => t("For 'Cash / Check / Bank Transfer / Other', the order will remain on the 'Awaiting payment' status until the restaurant or the administrator approves it"),
            ])
            ->add('instructions', TextareaType::class, [
                'label' => t('Instructions'),
                'purify_html' => true,
                'required' => false,
                'attr' => ['class' => 'wysiwyg'],
            ])
            ->add('gatewayLogoFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Image'),
                'translation_domain' => 'messages',
            ])
            ->add('enabled', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Status'),
                'choices' => ['Disabled' => false, 'Enabled' => true],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            ->add('number', IntegerType::class, [
                'label' => t('Order of appearance'),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 1,
                        'max' => 30])],
                'attr' => ['class' => 'touchspin-integer'],
            ])
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
            'validation_groups' => ['create', 'update'],
        ]);
    }
}
