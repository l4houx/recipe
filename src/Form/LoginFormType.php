<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function Symfony\Component\Translation\t;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Profil
            ->add('username', TextType::class, [
                'label' => t('User name or email :'),
                // 'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'placeholder' => t('Email address here'),
                    'autocomplete' => 'username',
                    'autofocus' => true,
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => t('Password :'),
                // 'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'placeholder' => t('**************'),
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('_remember_me', CheckboxType::class, [
                'label' => t('Remember me :'),
                'mapped' => false,
                'data' => true, // Default checked
            ])
        ;
    }

    public function getBlockPrefix(): ?string
    {
        return '';
    }
}
