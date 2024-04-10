<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use function Symfony\Component\Translation\t;

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => t('User name :'),
                'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'attr' => ['placeholder' => t('User name')],
            ])
            ->add('email', EmailType::class, [
                'label' => t('Email address :'),
                'purify_html' => true,
                'required' => false,
                'attr' => ['placeholder' => t('Email address here')],
            ])
            ->add('content', TextareaType::class, [
                'label' => t('Comment :'),
                'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
                'help' => t('Comments that do not comply with our code of conduct will be moderated.'),
            ])
            ->add('isRGPD', CheckboxType::class, [
                'label' => t('Yes, I accept the privacy policy'),
                'data' => true, // Default checked
                'constraints' => [
                    new NotBlank(['message' => t("Please don't leave the GDPR blank!")]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
