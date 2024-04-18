<?php

namespace App\Form;

use App\Entity\Setting\Menu;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class MenuFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Menu name'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            ->add('header', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Header text'),
            ])
            ->add('menuElements', CollectionType::class, [
                'label' => t('Menu elements'),
                'entry_type' => MenuElementFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__menuelements__',
                'required' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'menuelements-collection form-collection manual-init',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => t('Save'),
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
