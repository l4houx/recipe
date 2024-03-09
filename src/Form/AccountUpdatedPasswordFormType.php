<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/** ChangePasswordFormType */
class AccountUpdatedPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $editAttr = ['minlength' => 8];

        if ($options['current_password_is_required']) {
            $builder
                ->add('currentPassword', PasswordType::class, [
                    'label' => 'Mot de passe actuel :',
                    'mapped' => false,
                    'attr' => [
                        'autocomplete' => 'off',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer votre mot de passe actuel',
                        ]),
                        new UserPassword(['message' => 'Mot de passe actuel invalide.']),
                    ],
                ])
            ;
        }

        $builder
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'hash_property_path' => 'password', 
                    'label' => 'Nouveau mot de passe :', 
                    'attr' => [...$editAttr, ...['placeholder' => '**************']]
                ],
                'second_options' => [
                    'label' => 'Confirmez votre nouveau mot de passe :', 
                    'attr' => [...$editAttr, ...['placeholder' => '**************']]
                ],
                'translation_domain' => 'message',
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                        'htmlPattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$',
                        'groups' => ['password'],
                    ]),
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                        'max' => 128,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'current_password_is_required' => false,
        ]);

        $resolver->setAllowedTypes('current_password_is_required', 'bool');
    }
}
