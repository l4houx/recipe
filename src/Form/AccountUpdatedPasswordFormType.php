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

use function Symfony\Component\Translation\t;

/** ChangePasswordFormType */
class AccountUpdatedPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $editAttr = ['minlength' => 16];

        if ($options['current_password_is_required']) {
            $builder
                ->add('currentPassword', PasswordType::class, [
                    'label' => t('Current Password :'),
                    'mapped' => false,
                    'attr' => [
                        'autocomplete' => 'off',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => t('Please enter your current password'),
                        ]),
                        new UserPassword(['message' => t('Current password is invalid.')]),
                    ],
                ])
            ;
        }

        $builder
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'hash_property_path' => 'password', 
                    'label' => t('New Password :'), 
                    'attr' => [...$editAttr, ...['placeholder' => '**************']]
                ],
                'second_options' => [
                    'label' => t('Confirm your new password :'), 
                    'attr' => [...$editAttr, ...['placeholder' => '**************']]
                ],
                'translation_domain' => 'messages',
                'invalid_message' => t('Password fields must correspond.'),
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{16,}$/',
                        'htmlPattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{16,}$',
                        'groups' => ['password'],
                    ]),
                    new NotBlank([
                        'message' => t('Please enter a password'),
                    ]),
                    new Length([
                        'min' => 16,
                        'minMessage' => t('Your password must have at least {{ limit }} characters'),
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
