<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Traits\HasRoles;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
//use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
//use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordAttrs = ['minlength' => 16];

        $builder
            // Profil
            ->add('username', TextType::class, [
                'label' => t('User name :'),
                // 'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => t('User name')],
            ])
            ->add('slug', HiddenType::class, [
                'empty_data' => '',
                'required' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => t('First name :'),
                // 'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => t('First name')],
            ])
            ->add('lastname', TextType::class, [
                'label' => t('Last name :'),
                // 'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => t('Last name')],
            ])
            // Password
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    // 'purify_html' => true,
                    'toggle' => true,
                    'translation_domain' => 'messages',
                    'attr' => [
                        'placeholder' => t('Password'),
                        'autocomplete' => 'new-password',
                    ],
                ],
                'label_attr' => ['class' => 'form-label'],
                'first_options' => ['label' => t('Password :'), 'attr' => [...$passwordAttrs, ...['placeholder' => '**************']]],
                'second_options' => ['label' => t('Confirm password :'), 'attr' => [...$passwordAttrs, ...['placeholder' => '**************']]],
                'invalid_message' => t('Password fields must correspond.'),
                'mapped' => false,
                'required' => true,
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
            // Contact
            ->add('email', EmailType::class, [
                'label' => t('Email address :'),
                // 'purify_html' => true,
                'required' => true,
                'attr' => ['placeholder' => t('Email address here')],
            ])
            // Role
            ->add('roles', ChoiceType::class, [
                'label' => t('Roles :'),
                'required' => false,
                'choices' => [
                    t('Admin') => HasRoles::ADMIN,
                    t('Moderator') => HasRoles::MODERATOR,
                    t('Team') => HasRoles::TEAM,
                    t('User') => HasRoles::DEFAULT,
                ],
                'multiple' => true,
            ])
            // Team
            ->add('designation', TextType::class, [
                'label' => t('Designation :'),
                // 'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => ''],
            ])
            ->add('about', TextareaType::class, [
                'label' => t('About :'),
                // 'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            // Agree Terms
            ->add('isAgreeTerms', CheckboxType::class, [
                'mapped' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => t("You must accept the conditions of use of your personal data."),
                    ]),
                ],
            ])
            // Verified
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
            ])
            /*
            ->add('recaptcha', EWZRecaptchaType::class, [
                'attr' => [
                    'options' => [
                        'theme' => 'light',
                        'type' => 'image',
                        'size' => 'normal',
                    ],
                ],
                'mapped' => false,
                'constraints' => [
                    new RecaptchaTrue(['groups' => 'User']),
                ],
            ])
            */
            ->add(t('Save'), SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
