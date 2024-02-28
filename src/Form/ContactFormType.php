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
                'label' => 'Nom :',
                'empty_data' => '',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail :',
                'empty_data' => '',
            ])
            ->add('message', TextareaType::class, [
                'empty_data' => '',
                'attr' => [
                    'rows' => 10,
                    'cols' => 30,
                ],
            ])
            ->add('service', ChoiceType::class, [
                'choices' => [
                    'Support' => $this->params->get('website_support'),
                    'Marketing' => $this->params->get('website_marketing'),
                    'Compta' => $this->params->get('website_compta'),
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
