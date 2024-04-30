<?php

namespace App\Form;

use App\Entity\Keyword;
use App\Entity\Post;
use App\Entity\PostCategory;
use App\Entity\User;
use App\Form\Type\SwitchType;
use App\Entity\PostType as Type;
use App\Service\SettingService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

use function Symfony\Component\Translation\t;

class PostFormType extends AbstractType
{
    public function __construct(
        private readonly SettingService $settingService
    ) {
        // code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Main blog post image :'),
                'translation_domain' => 'messages'
            ])
            ->add('title', TextType::class, [
                'label' => t('Title :'),
                'required' => true,
                'purify_html' => true,
                'empty_data' => '',
                'help' => t('Keep your post titles under 10 characters. Write heading that describe the topic content. Contextualize for Your Post.'),
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
                'purify_html' => true,
                'empty_data' => '',
                'attr' => ['class' => 'wysiwyg', 'placeholder' => '', 'rows' => 6],
                'help' => t(''),
            ])
            ->add('type', EntityType::class, [
                'required' => true,
                'class' => Type::class,
                'placeholder' => t('Choose a type'),
                'choice_label' => 'name',
                'autocomplete' => true,
                'label' => t('Type :'),
                'attr' => ['data-limit' => 1],
                'query_builder' => function () {
                    return $this->settingService->getPostsTypes([]);
                },
            ])
            ->add('author', UserAutocompleteField::class, ['label' => t('Author :')])
            ->add('category', PostCategoryAutocompleteField::class)
            /*
            ->add('category', EntityType::class, [
                'label' => t('Category :'),
                'class' => PostCategory::class,
                'choice_label' => 'name',
                'required' => true,
                'empty_data' => '',
                'multiple' => false,
                'autocomplete' => true,
                'attr' => [
                    'class' => 'form-select',
                    'data-limit' => 1,
                ],
                'help' => t('Make sure you select the correct category to allow users to find it quickly.'),
                'query_builder' => function () {
                    return $this->settingService->getBlogPostCategories([]);
                },
                
            ])*/
            ->add('tags', TextType::class, [
                'label' => t('Tags :'),
                'purify_html' => true,
                'empty_data' => '',
                'attr' => [
                    'class' => 'tags-input',
                    'data-limit' => 1,
                ],
                'help' => t('Make sure you select the correct keyword to allow users to find it quickly.'),
            ])
            ->add('readtime', TextType::class, [
                'label' => t('Reading time in minutes'),
                'required' => false,
                'purify_html' => true,
                'attr' => ['class' => 'touchspin-integer', 'data-min' => 1, 'data-max' => 1000000],
            ])
            ->add('isOnline', SwitchType::class, ['label' => t('Online')])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
