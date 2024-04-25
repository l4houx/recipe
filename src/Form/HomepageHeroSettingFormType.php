<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Recipe;
use App\Service\SettingService;
use App\Repository\UserRepository;
use App\Repository\RecipeRepository;
use Symfony\Component\Form\AbstractType;
use App\Entity\Setting\HomepageHeroSetting;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
                'purify_html' => true,
            ])
            ->add('paragraph', TextareaType::class, [
                'label' => t('Paragraph :'),
                'required' => false,
                'purify_html' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            ->add('content', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('What to show in the homepage hero ?'),
                'choices' => ['Hide slider' => 'none', 'Recipes slider' => 'recipes', 'Restaurants slider' => 'restaurants', 'Custom hero' => 'custom'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('recipes', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'class' => Recipe::class,
                'choice_label' => 'title',
                'placeholder' => t('Choose a recipe'),
                'attr' => ['class' => 'form-select'],
                'label' => t('Recipes'),
                'query_builder' => function (RecipeRepository $recipeRepository) {
                    return $recipeRepository->createQueryBuilder('recipe');
                },
            ])
            ->add('restaurants', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'autocomplete' => true,
                'class' => User::class,
                'choice_label' => 'username',
                'placeholder' => t('Choose a restaurant name'),
                'attr' => ['class' => 'form-select'],
                'label' => t('Restaurants'),
                'help' => t('Make sure to select restaurants who have added a cover photo'),
                'group_by' => 'restaurant.name',
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository
                        ->findOneBy(['roles' => 'restaurant'], [])
                    ;
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
            ->add('customBlockOneFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Custom hero image 1'),
                'translation_domain' => 'messages',
            ])
            ->add('customBlockTwoFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Custom hero image 2'),
                'translation_domain' => 'messages',
            ])
            ->add('customBlockThreeFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Custom hero image 3'),
                'translation_domain' => 'messages',
            ])
            ->add('show_search_box', ChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the homepage hero seach box'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            // Fields below are not stored in the entity, but in the settings cache
            ->add('homepage_show_search_box', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the search box'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'constraints' => [
                    new NotNull(),
                ],
            ])
            ->add('homepage_recipes_number', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Number of recipes to show'),
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
            ->add('homepage_testimonials_number', TextType::class, [
                'purify_html' => true,
                'mapped' => false,
                'required' => true,
                'label' => t('Number of testimonials to show'),
                'attr' => ['class' => 'touchspin-integer', 'data-min' => 0, 'data-max' => 15],
            ])
            ->add('homepage_show_call_to_action', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Show the call to action block'),
                'choices' => ['Disabled' => 0, 'Enabled' => 1],
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
