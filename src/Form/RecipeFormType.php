<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Category;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use function Symfony\Component\Translation\t;

class RecipeFormType extends AbstractType
{
    public function __construct(private FormListenerFactory $formListenerFactory)
    {
        # code...
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('thumbnailFile', VichFileType::class, [
                'label' => t('Image :'),
            ])
            //->add('thumbnailFile', FileType::class)
            ->add('title', TextType::class, [
                'label' => t('Title :'),
                'required' => true,
                // 'purify_html' => true,
                'empty_data' => '',
                'help' => t('Keep your recipe titles under 10 characters. Write heading that describe the topic content. Contextualize for Your Audience.'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            ->add('category', CategoryAutocompleteField::class, ['label' => t('Categorie :')])
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
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
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
                    #'data-controller' => 'form-collection',
                    'data-form-collection-add-label-value' => t('Add an ingredient'),
                    'data-form-collection-delete-label-value' => t('Remove an ingredient')
                ]
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->slug('title'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->timestamps())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
