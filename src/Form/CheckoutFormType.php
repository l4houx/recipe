<?php

namespace App\Form;

use App\Entity\Country;
use App\Service\SettingService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use function Symfony\Component\Translation\t;

class CheckoutFormType extends AbstractType
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
        // code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderReference', HiddenType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['creator', 'pos']]),
                ],
            ])
            ->add('firstname', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('First name :'),
                'constraints' => [
                    new NotBlank(['groups' => ['creator']]),
                    new Length([
                        'min' => 2,
                        'max' => 20,
                        'groups' => ['creator', 'pos']]),
                ],
            ])
            ->add('lastname', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Last name :'),
                'constraints' => [
                    new NotBlank(['groups' => ['creator']]),
                    new Length([
                        'min' => 2,
                        'max' => 20,
                        'groups' => ['creator', 'pos']]),
                ],
            ])
            ->add('email', EmailType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Email address :'),
                'constraints' => [
                    new Assert\Email(['groups' => ['creator']]),
                    new NotBlank(['groups' => ['creator']]),
                    new Length([
                        'min' => 5,
                        'max' => 180,
                        'groups' => ['creator']]),
                ],
            ])
            ->add('country', EntityType::class, [
                'required' => true,
                'class' => Country::class,
                'choice_label' => 'name',
                'label' => t('Country :'),
                'attr' => ['class' => 'select2'],
                'placeholder' => t('Select an option'),
                'constraints' => [
                    new NotBlank(['groups' => ['creator']]),
                ],
                'query_builder' => function () {
                    return $this->settingService->getCountries([]);
                },
            ])
            ->add('state', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('State :'),
                'constraints' => [
                    new NotBlank(['groups' => ['creator']]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'groups' => ['creator']]),
                ],
            ])
            ->add('city', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('City :'),
                'constraints' => [
                    new NotBlank(['groups' => ['creator']]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'groups' => ['creator']]),
                ],
            ])
            ->add('postalcode', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Postal code :'),
                'constraints' => [
                    new NotBlank(['groups' => ['creator']]),
                    new Length([
                        'min' => 2,
                        'max' => 15,
                        'groups' => ['creator']]),
                ],
            ])
            ->add('street', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Street :'),
                'constraints' => [
                    new NotBlank(['groups' => ['creator']]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'groups' => ['creator']]),
                ],
            ])
            ->add('street2', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Street 2 :'),
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'groups' => ['creator'],
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['creator', 'pos'],
        ]);
    }
}
