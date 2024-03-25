<?php

namespace App\Form;

use App\Entity\HelpCenterCategory;
use App\Form\Type\SwitchType;
use App\Service\SettingService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

// use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class HelpCenterCategoryType extends AbstractType
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*
                ->add('parent', EntityType::class, [
                    'required' => false,
                    'multiple' => false,
                    'class' => HelpCenterCategory::class,
                    'choice_label' => 'name',
                    'attr' => ['class' => 'select2'],
                    'label' => 'Parent',
                    'help' => t('Select the parent category to add a sub category'),
                    'query_builder' => function() {
                        return $this->settingService->getHelpCenterCategories(["parent" => "none"]);
                    }
                ])
            */
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'required' => true,
                // 'purify_html' => true,
            ])
            ->add('icon', TextType::class, [
                'label' => t('Icon'),
                'required' => false,
                // 'purify_html' => true,
                'attr' => ['class' => 'icon-picker', 'autocomplete' => 'disabled'],
            ])
            ->add('color', ColorType::class, [
                'label' => t('Color :'),
                'empty_data' => '',
                'required' => true,
            ])
            ->add('color', ChoiceType::class, [
                'label' => t('Color'),
                'required' => false,
                'multiple' => false,
                'expanded' => true,
                'choices' => ['label.light' => 'text-bg-light', 'label.secondary' => 'text-bg-secondary', 'label.warning' => 'text-bg-warning',
                    'label.info' => 'text-bg-info', 'label.primary' => 'text-bg-primary', 'label.danger' => 'text-bg-danger', 'label.success' => 'text-bg-success', 'label.dark' => 'text-bg-dark'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => 'help.color',
            ])
            ->add('isOnline', SwitchType::class, ['label' => t('Online')])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HelpCenterCategory::class,
        ]);
    }
}
