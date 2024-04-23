<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\String\u;

class DateTimePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => false,
            'attr' => [
                'class' => 'text-dark flatpickr flatpickr-input active',
                'data-flatpickr-class' => 'standard',
                'data-date-locale' => u(\Locale::getDefault())->replace('_', '-')->lower(),
                'data-date-format' => 'Y-m-d H:i',
            ],
            'format' => 'yyyy-MM-dd HH:mm',
            'input_format' => 'Y-m-d H:i',
            'date_format' => 'Y-m-d H:i',
        ]);
    }

    public function getParent(): ?string
    {
        return DateTimeType::class;
    }
}
