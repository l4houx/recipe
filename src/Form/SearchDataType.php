<?php

declare(strict_types=1);

namespace App\Form;

use App\DTO\SearchDataDTO;
use App\Entity\PostCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use function Symfony\Component\Translation\t;

class SearchDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keywords', TextType::class, [
                'attr' => [
                    'placeholder' => t('Keywords'),
                    'aria-label' => t('Search'),
                ],
                'empty_data' => '',
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'class' => PostCategory::class,
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'name',
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchDataDTO::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
