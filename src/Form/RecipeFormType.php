<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Country;
use App\Entity\Audience;
use App\Entity\Category;
use App\Service\SettingService;
use App\Entity\Setting\Language;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RecipeFormType extends AbstractType
{
    public function __construct(private readonly SettingService $settingService)
    {
        // code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Main recipe image'),
                'help' => t('Choose the right image to represent your recipe (We recommend using at least a 1200x600px (2:1 ratio) image )'),
                'translation_domain' => 'messages',
            ])
            ->add('images', CollectionType::class, [
                'label' => t('Images gallery'),
                'entry_type' => RecipeImageFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'required' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-collection',
                ],
                'help' => t('Add other images that represent your recipe to be displayed as a gallery'),
                'error_bubbling' => false,
            ])
            ->add('title', TextType::class, [
                'label' => t('Title :'),
                'required' => true,
                'purify_html' => true,
                'empty_data' => '',
                'help' => t('Keep your recipe titles under 10 characters. Write heading that describe the topic content. Contextualize for Your Recipe.'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            ->add('category', CategoryAutocompleteField::class)
            /*
            ->add('category', EntityType::class, [
                'label' => t('Categorie :'),
                "class" => Category::class,
                "choice_label" => "name",
                'autocomplete' => true,
                //'expanded' => true,
                'empty_data' => '',
            ])
            */
            ->add('content', TextareaType::class, [
                'label' => t('Content :'),
                'required' => true,
                'purify_html' => true,
                'empty_data' => '',
                'attr' => ['class' => 'wysiwyg', 'placeholder' => '', 'rows' => 6],
                'help' => t(''),
            ])
            ->add('duration', null, [
                'label' => t('Duration :'),
            ])
            ->add('quantities', CollectionType::class, [
                'entry_type' => QuantityFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => ['label' => false],
                'attr' => [
                    // 'data-controller' => 'form-collection',
                    'data-form-collection-add-label-value' => t('Add an ingredient'),
                    'data-form-collection-delete-label-value' => t('Remove an ingredient'),
                ],
            ])
            ->add('showattendees', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Attendees'),
                'choices' => ['Hide' => false, 'Show' => true],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Show the attendees number and list in the recipe page'),
            ])
            ->add('enablereviews', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Enable reviews'),
                'choices' => ['Enable' => true, 'Disable' => false],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            ->add('languages', EntityType::class, [
                'required' => false,
                'autocomplete' => true,
                'multiple' => true,
                'expanded' => false,
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => t('Languages'),
                'help' => t('Select the languages that will be spoken in your recipe'),
                'attr' => ['class' => 'select2'],
                'query_builder' => function () {
                    return $this->settingService->getLanguages([]);
                },
            ])
            ->add('subtitles', EntityType::class, [
                'required' => false,
                'autocomplete' => true,
                'multiple' => true,
                'expanded' => false,
                'class' => Language::class,
                'choice_label' => 'name',
                'label' => t('Subtitles'),
                'help' => t('If your recipe is a movie for example, select the available subtitles'),
                'attr' => ['class' => 'select2'],
                'query_builder' => function () {
                    return $this->settingService->getLanguages([]);
                },
            ])
            ->add('year', ChoiceType::class, [
                'required' => false,
                'label' => t('Year :'),
                'choices' => $this->getYears(1900),
                'help' => t('If your recipe is a movie for example, select the year of release'),
                'attr' => ['class' => 'select2', 'data-sort-options' => '0'],
            ])
            ->add('audiences', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'class' => Audience::class,
                'choice_label' => 'name',
                'label' => t('Audiences :'),
                'help' => t('Select the audience types that are targeted in your recipe'),
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline'],
                'query_builder' => function () {
                    return $this->settingService->getAudiences([]);
                },
            ])
            /*
            ->add('country', EntityType::class, [
                'required' => false,
                'class' => Country::class,
                'choice_label' => 'name',
                'label' => t('Country :'),
                'help' => t("Select the country that your recipe represents (ie: A movie's country of production)"),
                'query_builder' => function () {
                    return $this->settingService->getCountries([]);
                },
                'attr' => ['class' => 'select2', 'data-sort-options' => '1']
            ])
            */
            ->add('country', CountryAutocompleteField::class, ['required' => false])
            ->add('youtubeurl', UrlType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Youtube video url :'),
                'help' => t('If you have an Youtube video that represents your activities as an recipe restaurant, add it in the standard format: https://www.youtube.com/watch?v=FzG4uDgje3M'),
            ])
            ->add('externallink', UrlType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('External link :'),
                'help' => t('If your recipe has a dedicated website, enter its url here'),
            ])
            ->add('phone', TelType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Contact phone number :'),
                'help' => t('Enter the phone number to be called for inquiries'),
            ])
            ->add('email', EmailType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Contact email address :'),
                'help' => t('Enter the email address to be reached for inquiries'),
            ])
            ->add('twitterurl', UrlType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Twitter :'),
            ])
            ->add('instagramurl', UrlType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Instagram :'),
            ])
            ->add('facebookurl', UrlType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Facebook :'),
            ])
            ->add('googleplusurl', UrlType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Google plus :'),
            ])
            ->add('linkedinurl', UrlType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('LinkedIn :'),
            ])
            ->add('authors', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Authors :'),
                'help' => t('Enter the list of authors that will perform in your recipe (press Enter after each entry)'),
                'attr' => ['class' => 'tags-input'],
            ])
            ->add('tags', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Tags :'),
                'help' => t('To help attendee find your recipe quickly, enter some keywords that identify your recipe (press Enter after each entry)'),
                'attr' => ['class' => 'tags-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
            'validation_groups' => ['create', 'update'],
        ]);
    }

    private function getYears($min): array
    {
        $years = range(date('Y', strtotime('+2 years')), $min);

        return array_combine($years, $years);
    }
}
