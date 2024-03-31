<?php

namespace App\Form;

use App\Entity\PointOfSale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

use function Symfony\Component\Translation\t;

class PointOfSaleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Name :'),
            ])
            ->add('username', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Username :'),
                'constraints' => [
                    new NotBlank(['groups' => ['create', 'update']]),
                    new Length([
                        'min' => 2,
                        'max' => 15,
                        'groups' => ['create', 'update'],
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'invalid_message' => t('The password fields must match.'),
                'options' => ['purify_html' => true, 'toggle' => true],
                'first_options' => ['required' => true, 'label' => t('Password :'), 'constraints' => [
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{16,}$/',
                        'htmlPattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{16,}$',
                        'groups' => ['password', 'create', 'update'],
                    ]),
                    new NotBlank(['groups' => ['create']]),
                    new Length([
                        'min' => 16,
                        'max' => 4096,
                        'groups' => ['create', 'update'],
                    ]),
                ], ],
                'second_options' => ['required' => true, 'label' => t('Repeat password :')],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PointOfSale::class,
            'validation_groups' => ['create', 'update'],
        ]);
    }
}
