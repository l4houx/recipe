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
                'label' => 'Image :',
            ])
            //->add('thumbnailFile', FileType::class)
            ->add('title', TextType::class, [
                'label' => 'Titre :',
                'empty_data' => '',
            ])
            ->add('slug', TextType::class, [
                'empty_data' => '',
                'required' => false,
            ])
            ->add('category', CategoryAutocompleteField::class)
            /*
            ->add('category', EntityType::class, [
                'label' => 'Catégorie :',
                "class" => Category::class,
                "choice_label" => "name",
                'autocomplete' => true,
                //'expanded' => true,
                'empty_data' => '',
            ])
            */
            ->add('content', TextareaType::class, [
                'label' => 'Contenu :',
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            ->add('duration')
            ->add('quantities', CollectionType::class, [
                'entry_type' => QuantityFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => ['label' => false],
                'attr' => [
                    #'data-controller' => 'form-collection',
                    'data-form-collection-add-label-value' => 'Ajouter un ingrèdient',
                    'data-form-collection-delete-label-value' => 'Supprimer un ingrèdient'
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
