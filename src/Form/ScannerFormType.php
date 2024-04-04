<?php

namespace App\Form;

use App\Entity\Scanner;
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

class ScannerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordAttrs = ['minlength' => 16];

        $builder
            ->add('username', TextType::class, [
                'label' => t('User name :'),
                'purify_html' => true,
                'required' => true,
                'mapped' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => t('User name')],
                'constraints' => [
                    new NotBlank(['groups' => ['create', 'update']]),
                    new Length([
                        'min' => 2,
                        'max' => 15,
                        'groups' => ['create', 'update'],
                    ]),
                ],
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            ->add('name', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Name :'),
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'invalid_message' => t('Password fields must correspond.'),
                'options' => [
                    'purify_html' => true,
                    'toggle' => true,
                    'translation_domain' => 'messages',
                ],
                'first_options' => ['required' => true, 'label' => t('Password :'), 'constraints' => [
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{16,}$/',
                        'htmlPattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{16,}$',
                        'groups' => ['password'],
                    ]),
                    new NotBlank(['groups' => ['create']]),
                    new Length([
                        'min' => 16,
                        'minMessage' => t('Your password must have at least {{ limit }} characters'),
                        'max' => 128,
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
            'data_class' => Scanner::class,
            'validation_groups' => ['create', 'update'],
        ]);
    }
}
