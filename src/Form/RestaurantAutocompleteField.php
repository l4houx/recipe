<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Recipe;
use App\Repository\UserRepository;
use App\Repository\RecipeRepository;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class RestaurantAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('Restaurants'),
            'class' => User::class,
            'required' => false,
            'multiple' => true,
            'placeholder' => t('Choose a restaurant name'),
            'choice_label' => 'restaurant.name',
            'query_builder' => function (UserRepository $userRepository) {
                return $userRepository->createQueryBuilder('user');
            },
            'help' => t('Make sure to select restaurants who have added a cover photo'),
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
