<?php

declare(strict_types=1);

namespace App\Form;

// use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
// use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostSharedFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('sender_name', TextType::class, [
                'label' => 'Nom et prénom :',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('sender_email', EmailType::class, [
                'label' => 'Adresse e-mail :',
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Length(min: 5, max: 180),
                ],
            ])
            ->add('receiver_email', EmailType::class, [
                'label' => "Courriel d'un ami :",
                'required' => true,
                'empty_data' => '',
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Length(min: 5, max: 180),
                ],
            ])
            ->add('sender_comments', TextareaType::class, [
                'label' => 'Commentaire :',
                'attr' => ['rows' => 6],
                'help' => 'Laissez-le vide si vous le souhaitez (facultatif).',
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
                    new RecaptchaTrue(['groups' => 'PostShared']),
                ],
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
