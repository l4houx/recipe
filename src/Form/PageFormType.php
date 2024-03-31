<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Setting\Page;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use function Symfony\Component\Translation\t;

class PageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('title', TextType::class, [
                'label' => t('Title :'),
                'required' => true,
                'purify_html' => true,
                'empty_data' => '',
                'help' => t('Keep your page titles under 10 characters. Write heading that describe the topic content. Contextualize for Your Audience.'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            ->add('content', TextareaType::class, [
                'label' => t('Content :'),
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
                'help' => t(''),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Page::class);
    }
}
