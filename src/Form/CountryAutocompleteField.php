<?php

namespace App\Form;

use App\Entity\Country;
use App\Service\SettingService;
use App\Repository\CountryRepository;
use Symfony\Component\Form\AbstractType;
use function Symfony\Component\Translation\t;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class CountryAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('Country :'),
            'class' => Country::class,
            'placeholder' => t('Choose a Country'),
            'choice_label' => 'name',
            'query_builder' => function (SettingService $settingService) {
                return $settingService->getCountries([]);
            },
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
