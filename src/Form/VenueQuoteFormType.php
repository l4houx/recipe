<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use function Symfony\Component\Translation\t;

class VenueQuoteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Email address :'),
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5]),
                ],
            ])
            ->add('phone', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Phone number :'),
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5]),
                ],
            ])
            ->add('guests', TextType::class, [
                'required' => false,
                'label' => t('Number of guests :'),
            ])
            ->add('note', TextareaType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Additional note :'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }
}
