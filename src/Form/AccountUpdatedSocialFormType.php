<?php

declare(strict_types=1);

namespace App\Form;

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
                'attr' => ['placeholder' => 'URL du lien externe', 'class' => 'mb-1'],
            ])
            ->add('twitterurl', TextType::class, [
                'label' => 'URL du profil Twitter :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => "Ajoutez l'URL de votre profil Twitter.",
                'attr' => ['placeholder' => 'URL du profil Twitter', 'class' => 'mb-1'],
            ])
            ->add('instagramurl', TextType::class, [
                'label' => 'URL du profil Instagram :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => "Ajoutez l'URL de votre profil Instagram.",
                'attr' => ['placeholder' => 'URL du profil Instagram', 'class' => 'mb-1'],
            ])
            ->add('facebookurl', TextType::class, [
                'label' => 'URL du profil Facebook :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => "Ajoutez l'URL de votre profil Facebook.",
                'attr' => ['placeholder' => 'URL du profil Facebook', 'class' => 'mb-1'],
            ])
            ->add('googleplusurl', TextType::class, [
                'label' => 'URL du profil Google Plus :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => "Ajoutez l'URL de votre profil Google Plus.",
                'attr' => ['placeholder' => 'URL du profil Google Plus', 'class' => 'mb-1'],
            ])
            ->add('linkedinurl', TextType::class, [
                'label' => 'URL du profil LinkedIn :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => "Ajoutez l'URL de votre profil LinkedIn",
                'attr' => ['placeholder' => 'URL du profil LinkedIn', 'class' => 'mb-1'],
            ])

            // Video
            ->add('youtubeurl', TextType::class, [
                'label' => 'URL du profil Youtube :',
                //'purify_html' => true,
                'required' => false,
                'empty_data' => '',
                'help' => "Ajoutez l'URL de votre profil Youtube.",
                'attr' => ['placeholder' => 'URL YouTube', 'class' => 'mb-1'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', AccountUpdatedSocialDTO::class);
    }
}
