<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Setting\HomepageHeroSetting;
use App\Entity\User;
use App\Service\SettingService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Vich\UploaderBundle\Form\Type\VichImageType;

use function Symfony\Component\Translation\t;

class HomepageHeroSettingFormType extends AbstractType
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
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
            ->add('paragraph', TextareaType::class, [
                'label' => t('Paragraph :'),
                'required' => false,
                // 'purify_html' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
                ->add('content', ChoiceType::class, [
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'label' => t('What to show in the homepage hero ?'),
                    'choices' => ['Hide slider' => 'none', 'Recipes slider' => 'recipes', 'Organizers slider' => 'organizers', 'Custom hero' => 'custom'],
                    'label_attr' => ['class' => 'radio-custom radio-inline'],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
                ->add('recipes', EntityType::class, [
                    'required' => false,
                    'multiple' => true,
                    'class' => Recipe::class,
                    'choice_label' => 'name',
                    'attr' => ['class' => 'select2'],
                    'label' => t('Recipes'),
                    'query_builder' => function () {
                        return $this->settingService->getRecipes(['elapsed' => 'all']);
                    },
                ])
                ->add('organizers', EntityType::class, [
                    'required' => false,
                    'multiple' => true,
                    'class' => User::class,
                    'choice_label' => 'organizer.name',
                    'attr' => ['class' => 'select2'],
                    'label' => t('Organizers'),
                    'help' => t('Make sure to select organizers who have added a cover photo'),
                    'query_builder' => function () {
                        return $this->settingService->getUsers(['roles' => 'organizer']);
                    },
                ])
                ->add('customBackgroundFile', VichImageType::class, [
                    'required' => false,
                    'allow_delete' => true,
                    'download_label' => false,
                    'download_uri' => false,
                    'image_uri' => false,
                    'imagine_pattern' => 'scale',
                    'label' => t('Custom hero background image'),
                    'translation_domain' => 'messages',
                ])
                ->add('show_search_box', ChoiceType::class, [
                    'required' => false,
                    'multiple' => false,
                    'expanded' => true,
                    'label' => t('Show the homepage hero seach box'),
                    'choices' => ['Yes' => true, 'No' => false],
                    'label_attr' => ['class' => 'radio-custom radio-inline'],
                ])
                // Fields below are not stored in the entity, but in the settings cache
                ->add('homepage_show_search_box', ChoiceType::class, [
                    'mapped' => false,
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'label' => t('Show the search box'),
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
                    'label_attr' => ['class' => 'radio-custom radio-inline'],
                    'constraints' => [
                        new NotNull(),
                    ],
                ])
                ->add('homepage_recipes_number', TextType::class, [
                    'purify_html' => true,
                    'mapped' => false,
                    'required' => true,
                    'label' => t('Number of events to show'),
                    'attr' => ['class' => 'touchspin-integer', 'data-min' => 0, 'data-max' => 36],
                ])
                ->add('homepage_categories_number', TextType::class, [
                    'purify_html' => true,
                    'mapped' => false,
                    'required' => true,
                    'label' => t('Number of featured categories to show'),
                    'attr' => ['class' => 'touchspin-integer', 'data-min' => 0, 'data-max' => 21],
                ])
                ->add('homepage_posts_number', TextType::class, [
                    'purify_html' => true,
                    'mapped' => false,
                    'required' => true,
                    'label' => t('Number of posts to show'),
                    'attr' => ['class' => 'touchspin-integer', 'data-min' => 0, 'data-max' => 15],
                ])
                ->add('homepage_show_call_to_action', ChoiceType::class, [
                    'mapped' => false,
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'label' => t('Show the call to action block'),
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
                    'label_attr' => ['class' => 'radio-custom radio-inline'],
                    'constraints' => [
                        new NotNull(),
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
            'data_class' => HomepageHeroSetting::class,
        ]);
    }
}
