<?php

namespace App\Form;

use App\Entity\Category;
use App\Form\Type\IconType;
use App\Form\Type\SwitchType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;

use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_label' => false,
                'download_uri' => false,
                'image_uri' => false,
                'imagine_pattern' => 'scale',
                'label' => t('Image :'),
                'translation_domain' => 'messages'
            ])
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'required' => true,
                'purify_html' => true,
                'empty_data' => '',
                'help' => t('Keep your category names under 10 characters. Write a name that describes the content of the topic. Contextualize for your audience..'),
            ])
            ->add('slug', TextType::class, [
                'label' => t('Slug :'),
                'empty_data' => '',
                'required' => false,
                'help' => t('Field must contain an unique value.'),
            ])
            /*
            ->add('recipes', EntityType::class, [
                "class" => Recipe::class,
                "choice_label" => "title",
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'empty_data' => '',
            ])
            */
            ->add('icon', IconType::class)
            ->add('color', ColorType::class, [
                'label' => t('Color :'),
                'purify_html' => true,
                'empty_data' => '',
                'required' => false,
            ])
            ->add('featuredorder', ChoiceType::class, [
                'required' => false,
                'label' => t('Featured order :'),
                'choices' => ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12, '13' => 13, '14' => 14, '15' => 15],
                'help' => t('Set the display order for the featured categories')
            ])
            ->add('isOnline', SwitchType::class, ['label' => t('Online')])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
