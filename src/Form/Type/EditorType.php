<?php

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditorType extends TextareaType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'html5' => false,
            'row_attr' => [
                'class' => 'full',
            ],
            'attr' => [
                'class' => 'wysiwyg',
                'placeholder' => '',
                'rows' => 10,
                'is' => 'markdown-editor',
            ],
        ]);
    }
}
