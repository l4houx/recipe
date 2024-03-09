<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\Quantity;
use App\Entity\Recipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity')
            ->add('unit')
            ->add('ingredient', EntityType::class, [
                'label' => 'Ingrédient :',
                'class' => Ingredient::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quantity::class,
        ]);
    }
}
