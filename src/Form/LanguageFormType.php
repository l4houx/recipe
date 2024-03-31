<?php

namespace App\Form;

use App\Entity\Setting\Language;
use App\Form\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class LanguageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('Name :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
                'help' => t('Keep your lang names under 10 characters. Write heading that describe the topic content. Contextualize for Your Audience.'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            ->add('code', TextType::class, [
                'label' => t('Language code :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => true,
            ])
            ->add('isOnline', SwitchType::class, ['label' => t('Online')])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Language::class,
        ]);
    }
}
