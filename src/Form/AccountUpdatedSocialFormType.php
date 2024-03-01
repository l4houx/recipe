<?php

declare(strict_types=1);

namespace App\Form\Update;

use App\Entity\User;
use App\DTO\AccountUpdatedSocialDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @method AccountUpdatedSocialDTO getData()
 */
class AccountUpdatedSocialFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            // Social Media
            ->add('externallink', TextType::class, [
                'label' => 'Lien externe :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => "Si vous disposez d'un site Web dédié, entrez son URL ici",
            ])
            ->add('twitterurl', TextType::class, [
                'label' => 'Twitter :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
            ])
            ->add('instagramurl', TextType::class, [
                'label' => 'Instagram :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
            ])
            ->add('facebookurl', TextType::class, [
                'label' => 'Facebook :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
            ])
            ->add('googleplusurl', TextType::class, [
                'label' => 'Google Plus :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
            ])
            ->add('linkedinurl', TextType::class, [
                'label' => 'LinkedIn :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
            ])

            // Video
            ->add('youtubeurl', TextType::class, [
                'label' => 'URL de vidéo Youtube :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => 'Si vous avez une vidéo Youtube qui représente vos activités, ajoutez-la au format standard: https://www.youtube.com/',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', AccountUpdatedSocialDTO::class);
    }
}
