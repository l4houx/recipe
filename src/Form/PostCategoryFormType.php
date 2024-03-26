<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\PostCategory;
use App\Service\SettingService;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;

class PostCategoryFormType extends AbstractType
{
    public function __construct(
        private FormListenerFactory $formListenerFactory,
        private readonly SettingService $settingService
    ) {
        # code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

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
            ->add('color', ColorType::class, [
                'label' => t('Color :'),
                'empty_data' => '',
                'required' => false,
            ])
            ->add('parent', EntityType::class, [
                'required' => false,
                'multiple' => false,
                'class' => PostCategory::class,
                'choice_label' => 'name',
                'label' => t('Parent'),
                'help' => t('Select the parent category to add a sub category'),
                'query_builder' => function() {
                    return $this->settingService->getBlogPostCategories(["parent" => "none"]);
                }
            ])
            ->add('posts', EntityType::class, [
                'class' => Post::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->slug('name'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->timestamps())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostCategory::class,
        ]);
    }
}
