<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

use function Symfony\Component\Translation\t;

#[AsEntityAutocompleteField]
class RecipeAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('Recipes'),
            'class' => Recipe::class,
            'required' => false,
            'placeholder' => t('Choose a recipe'),
            'choice_label' => 'title',
            'query_builder' => function (RecipeRepository $recipeRepository) {
                return $recipeRepository->createQueryBuilder('recipe');
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
