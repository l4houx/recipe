<?php

namespace App\Form;

use App\Entity\Country;
use App\Entity\User;
use App\Service\SettingService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

use function Symfony\Component\Translation\t;

class AccountCreatorFormType extends AbstractType
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Profil
            ->add('avatarFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Profile picture :'),
                'translation_domain' => 'messages'
            ])
            ->add('username', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('User name :'),
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 4,
                        'max' => 30]),
                ],
                'attr' => ['placeholder' => t('User name'), 'readonly' => true],
            ])
            ->add('slug', HiddenType::class, [
                'empty_data' => '',
                'required' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => t('First name :'),
                'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => t('First name')],
            ])
            ->add('lastname', TextType::class, [
                'label' => t('Last name :'),
                'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => t('Last name')],
            ])
            ->add('birthdate', DateType::class, [
                'required' => false,
                'label' => t('Birthday :'),
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datepicker flatpickr flatpickr-input',
                    'placeholder' => t('Birth of Date')
                ],
            ])
            // Contact
            ->add('email', EmailType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Email address :'),
                'constraints' => [
                    new Assert\Email(),
                    new NotBlank(),
                    new Length([
                        'min' => 5,
                        'max' => 180]),
                ],
                'attr' => ['placeholder' => t('Email address here'), 'readonly' => true],
            ])
            ->add('phone', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Phone :'),
                'attr' => ['placeholder' => t('Phone')],
            ])
            // Address
            ->add('street', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Street address :'),
                'attr' => ['placeholder' => t('Address')],
            ])
            ->add('street2', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Street address 2 :'),
                'attr' => ['placeholder' => t('Address')],
            ])
            ->add('city', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('City :'),
                'attr' => ['placeholder' => t('City')],
            ])
            ->add('postalcode', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Zip / Postal code :'),
                'attr' => ['placeholder' => t('Zip / Postal code')],
            ])
            ->add('state', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('State :'),
                'attr' => ['placeholder' => t('State')],
            ])
            ->add('country', EntityType::class, [
                'required' => false,
                'class' => Country::class,
                'choice_label' => 'name',
                'label' => t('Country :'),
                'multiple' => false,
                'autocomplete' => true,
                'attr' => ['class' => 'form-select'],
                'placeholder' => t('Select an option'),
                'query_builder' => function () {
                    return $this->settingService->getCountries([]);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
