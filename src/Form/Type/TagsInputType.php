<?php

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class TagsInputType extends \Symfony\Component\Form\Extension\Core\Type\TextType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'label' => 'Mots clés',
            'html5' => false,
            'widget' => 'single_text',
            'attr' => ['class' => 'tags-input'],
            'help' => 'Pour aider les articles, les recettes à trouver rapidement votre contenu, saisissez quelques mots-clés qui identifient votre événement (appuyez sur Entrée après chaque saisie)
            ',
        ]);
    }
}
