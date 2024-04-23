<?php

namespace App\Form;

use App\Entity\PostCategory;
use App\Service\SettingService;
use Symfony\Component\Form\AbstractType;
use App\Repository\PostCategoryRepository;
use function Symfony\Component\Translation\t;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class PostCategoryAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => t('Categorie :'),
            'class' => PostCategory::class,
            'placeholder' => t('Choose a category'),
            'choice_label' => 'name',
            'required' => true,
            'attr' => ['data-limit' => 1],
            'query_builder' => function (PostCategoryRepository $postCategoryRepository) {
                return $postCategoryRepository->createQueryBuilder('postcategory');
            },
            'help' => t('Make sure you select the correct category to allow users to find it quickly.'),
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
