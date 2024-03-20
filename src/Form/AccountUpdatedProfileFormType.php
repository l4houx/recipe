<?php

namespace App\Form;

use App\Entity\User;
use App\DTO\AccountUpdatedDTO;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * @method AccountUpdatedDTO getData()
 */
class AccountUpdatedProfileFormType extends AbstractType
{
    public function __construct(private FormListenerFactory $formListenerFactory)
    {
        // code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            // Profil
            ->add('username', TextType::class, [
                'label' => t("User name :"),
                // 'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => t("User name")],
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
            ->add('country', CountryType::class, [
                'label' => t('Country :'),
                'required' => true,
                'autocomplete' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => t('Eg : fr,en,de,eu')],
            ])
            // Contact
            ->add('email', EmailType::class, [
                'label' => t('Email address :'),
                // 'purify_html' => true,
                'required' => true,
                'attr' => ['placeholder' => t('Email address here')],
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
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->slug('username'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->timestamps())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            //'data_class' => User::class,
            'data_class' => AccountUpdatedDTO::class,
        ]);
    }
}
