<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Keyword;
use App\Entity\PostCategory;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\PostCategoryRepository;
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
                'label' => 'Image :',
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre :',
                'empty_data' => '',
                //'purify_html' => true,
            ])
            ->add('slug', HiddenType::class, [
                'empty_data' => '',
                'required' => false,
                //'purify_html' => true,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu :',
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
            ])
            ->add('author', EntityType::class, [
                'label' => 'Auteur :',
                'class' => User::class,
                'choice_label' => 'username',
                'empty_data' => '',
            ])
            //->add('createdAt')
            //->add('updatedAt')
            ->add('postcategories', EntityType::class, [
                'label' => 'Catégorie :',
                'class' => PostCategory::class,
                'choice_label' => 'name',
                'required' => true,
                'multiple' => true,
                //'expanded' => false,
                //'by_reference' => false,
                'empty_data' => '',
                'help' => 'Assurez-vous de sélectionner la bonne catégorie pour permettre aux utilisateurs de la trouver rapidement',
            ])
            ->add('keywords', EntityType::class, [
                'label' => 'Mot clé :',
                'class' => Keyword::class,
                'choice_label' => 'name',
                'multiple' => true,
                'empty_data' => '',
                'help' => 'Assurez-vous de sélectionner le bon mot-clé pour permettre aux utilisateurs de le trouver rapidement',
            ])
            ->add('readtime', TextType::class, [
                'label' => 'Temps de lecture en minutes',
                'required' => false,
                //'purify_html' => true,
                'attr' => ['class' => 'touchspin-integer', 'data-min' => 1, 'data-max' => 1000000],
            ])
            ->add('isOnline', CheckboxType::class, ['label' => 'En ligne'])
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
