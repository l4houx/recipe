<?php

namespace App\Form;

use App\DTO\HelpCenterSupportDTO;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class HelpCenterSupportFormType extends AbstractType
{
    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('Your name :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
                'attr' => ['placeholder' => t('Your name')],
            ])
            ->add('email', EmailType::class, [
                'label' => t('Email address :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
                'attr' => ['placeholder' => t('Email address here')],
            ])
            ->add('message', TextareaType::class, [
                'label' => t('Message :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
                'attr' => [
                    'placeholder' => t('Write down here'),
                    'rows' => 6
                ],
            ])
            ->add('service', ChoiceType::class, options: [
                'label' => t('Choose a service'),
                'required' => true,
                'choices' => [
                    'Support' => $this->params->get('website_support'),
                    'Marketing' => $this->params->get('website_marketing'),
                    'Accounting' => $this->params->get('website_compta'),
                ],
            ])
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
                    new RecaptchaTrue(['groups' => 'HelpConterSupport']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HelpCenterSupportDTO::class,
        ]);
    }
}
