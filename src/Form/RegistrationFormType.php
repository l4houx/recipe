<?php

namespace App\Form;

use App\Entity\User;
//use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
//use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegistrationFormType extends AbstractType
{
    public function __construct(private FormListenerFactory $formListenerFactory)
    {
        # code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordAttrs = ['minlength' => 8];

        $builder
            // Profil
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur :",
                //'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => "Nom d'utilisateur"],
            ])
            ->add('slug', HiddenType::class, [
                'empty_data' => '',
                'required' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom :',
                //'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => 'Prénom'],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom :',
                //'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => 'Nom'],
            ])
            // Contact
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail :',
                //'purify_html' => true,
                'required' => true,
                'attr' => ['placeholder' => 'Adresse email ici'],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => "Vous devez accepter les conditions d'utilisation de vos données personnelles.",
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    //'purify_html' => true,
                    'translation_domain' => 'message',
                    'attr' => [
                        'placeholder' => 'Mot de passe',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'label_attr' => ['class' => 'form-label'],
                'first_options' => ['label' => 'Mot de passe :', 'attr' => [...$passwordAttrs, ...['placeholder' => "**************"]]],
                'second_options' => ['label' => 'Confirmez le mot de passe :', 'attr' => [...$passwordAttrs, ...['placeholder' => "**************"]]],
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                'mapped' => false,
                'required' => true,
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
                        'max' => 4096,
                    ]),
                ],
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
                    new RecaptchaTrue(['groups' => 'Registration']),
                ],
            ])
            */
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->slug('username'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->timestamps())
        ;

        /*
        $builder
            ->add('username')
            ->add('agreeTerms', CheckboxType::class, [
                                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
        */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
