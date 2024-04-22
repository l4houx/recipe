<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

final class DropzoneType extends \Symfony\UX\Dropzone\Form\DropzoneType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'html5' => false,
            'widget' => 'single_text',
            'attr' => ['placeholder' => t('Drag and drop a file or click to browse')],
        ]);
    }
}
