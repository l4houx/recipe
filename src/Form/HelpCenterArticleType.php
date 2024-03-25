<?php

namespace App\Form;

use App\Entity\HelpCenterArticle;
use App\Entity\HelpCenterCategory;
use App\Form\Type\SwitchType;
use App\Service\SettingService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class HelpCenterArticleType extends AbstractType
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => t('Title :'),
                'empty_data' => '',
                'required' => true,
                // 'purify_html' => true,
            ])
            ->add('slug', HiddenType::class, [
                'empty_data' => '',
                'required' => false,
                // 'purify_html' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => t('Content :'),
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            ->add('tags', TextType::class, [
                'label' => t('Keywords :'),
                // 'purify_html' => true,
                'required' => false,
                'help' => t('Make sure you select the correct keyword to allow users to find it quickly.'),
            ])
            ->add('category', EntityType::class, [
                'label' => t('Category'),
                'required' => true,
                'multiple' => false,
                'attr' => ['class' => 'select2'],
                'class' => HelpCenterCategory::class,
                'choice_label' => 'name',
                'empty_data' => '',
                'attr' => [
                    'data-limit' => 1,
                ],
                'help' => t('Make sure to select right category to let the users find it quickly'),
                'query_builder' => function () {
                    return $this->settingService->getHelpCenterCategories([]);
                },
            ])
            ->add('isOnline', SwitchType::class, ['label' => t('Online')])
            ->add('isFeatured', SwitchType::class, ['label' => t('Featured')])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HelpCenterArticle::class,
        ]);
    }
}
