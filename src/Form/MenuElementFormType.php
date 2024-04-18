<?php

namespace App\Form;

use App\Entity\Setting\MenuElement;
use App\Service\SettingService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class MenuElementFormType extends AbstractType
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('icon', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Icon'),
                'attr' => ['class' => 'icon-picker', 'autocomplete' => 'disabled'],
            ])
            ->add('label', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Link text'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            ->add('link', ChoiceType::class, [
                'required' => false,
                'label' => t('Choose the link destination page'),
                'choices' => $this->settingService->getLinks(),
                'attr' => ['class' => 'select2', 'data-sort-options' => '0'],
            ])
            ->add('customLink', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Custom link'),
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'menuelement-position',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuElement::class,
        ]);
    }
}
