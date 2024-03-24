<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Keyword;
use App\Entity\PostCategory;
use App\Form\Type\SwitchType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\PostCategoryRepository;
use function Symfony\Component\Translation\t;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PostFormType extends AbstractType
{
    public function __construct(private FormListenerFactory $formListenerFactory)
    {
        # code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('thumbnailFile', VichFileType::class, [
                'label' => t('Image :'),
            ])
            ->add('title', TextType::class, [
                'label' => t('Title :'),
                'empty_data' => '',
                //'purify_html' => true,
            ])
            ->add('slug', HiddenType::class, [
                'empty_data' => '',
                'required' => false,
                //'purify_html' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => t('Content :'),
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            ->add('author', UserAutocompleteField::class, ['label' => t('Author :')])
            /*
            ->add('author', EntityType::class, [
                'label' => t('Author :'),
                'class' => User::class,
                'choice_label' => 'username',
                'autocomplete' => true,
                'empty_data' => '',
            ])
            */
            ->add('postcategories', EntityType::class, [
                'label' => t('Categorie :'),
                'class' => PostCategory::class,
                'choice_label' => 'name',
                'required' => true,
                'multiple' => true,
                //'expanded' => false,
                //'by_reference' => false,
                'empty_data' => '',
                'help' => t('Make sure you select the correct category to allow users to find it quickly.'),
            ])
            ->add('keywords', EntityType::class, [
                'label' => t('Keywords :'),
                'class' => Keyword::class,
                'choice_label' => 'name',
                'multiple' => true,
                'empty_data' => '',
                'attr' => [
                    'data-limit' => 1,
                ],
                'help' => t('Make sure you select the correct keyword to allow users to find it quickly.'),
            ])
            ->add('readtime', TextType::class, [
                'label' => t('Reading time in minutes'),
                'required' => false,
                //'purify_html' => true,
                'attr' => ['class' => 'touchspin-integer', 'data-min' => 1, 'data-max' => 1000000],
            ])
            ->add('isOnline', SwitchType::class, ['label' => t('Online')])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->slug('title'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->timestamps())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
