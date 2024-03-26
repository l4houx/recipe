<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;

use function Symfony\Component\Translation\t;

class CategoryFormType extends AbstractType
{
    public function __construct(private FormListenerFactory $formListenerFactory)
    {
        # code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'required' => true,
                // 'purify_html' => true,
                'empty_data' => '',
                'help' => t('Keep your category names under 10 characters. Write a name that describes the content of the topic. Contextualize for your audience..'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            /*
            ->add('recipes', EntityType::class, [
                "class" => Recipe::class,
                "choice_label" => "title",
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'empty_data' => '',
            ])
            */
            ->add('color', ColorType::class, [
                'label' => t('Color :'),
                'empty_data' => '',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->slug('name'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->timestamps())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
