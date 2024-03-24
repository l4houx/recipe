<?php

namespace App\Form;

use App\Entity\Traits\HasRoles;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use function Symfony\Component\Translation\t;

#[AsEntityAutocompleteField]
class UserAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => User::class,
            'placeholder' => t('Choose a User'),
            'choice_label' => 'username',

            'query_builder' => function (UserRepository $userRepository) {
                return $userRepository->createQueryBuilder('user');
            },
            'security' => HasRoles::ADMINAPPLICATION,
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
