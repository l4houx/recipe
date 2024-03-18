<?php

namespace App\Form;

use App\DTO\ContactFormDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use function Symfony\Component\Translation\t;

class ContactFormType extends AbstractType
{
    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('Name :'),
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'label' => t('Email address :'),
                'empty_data' => '',
            ])
            ->add('message', TextareaType::class, [
                'label' => t('Message :'),
                'empty_data' => '',
                'attr' => [
                    'rows' => 10,
                    'cols' => 30,
                ],
            ])
            ->add('service', ChoiceType::class, options: [
                'label' => t('Choose a service :'),
                'required' => true,
                'choices' => [
                    'Support' => $this->params->get('website_support'),
                    'Marketing' => $this->params->get('website_marketing'),
                    'Accounting' => $this->params->get('website_compta'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactFormDTO::class,
        ]);
    }
}
