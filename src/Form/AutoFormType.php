<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Form\Type\DateTimePickerType;
use App\Form\Type\EditorType;
use App\Form\Type\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class AutoFormType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    final public const TYPES = [
        'string' => TextType::class,
        'bool' => SwitchType::class,
        'int' => NumberType::class,
        'float' => NumberType::class,
        \DateTimeInterface::class => DateTimePickerType::class,
        UploadedFile::class => FileType::class,
    ];

    final public const NAMES = [
        'content' => EditorType::class,
        'description' => TextareaType::class,
        'short' => TextareaType::class,
        'color' => ColorType::class,
        'links' => TextareaType::class,
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];
        $refClass = new \ReflectionClass($data);
        $classProperties = $refClass->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($classProperties as $property) {
            $name = $property->getName();
            /** @var \ReflectionNamedType|null $type */
            $type = $property->getType();

            if (null === $type) {
                return;
            }

            if ('requirements' === $name) {
                $builder->add('requirements', ChoiceType::class, [
                    'multiple' => true,
                ]);
            }
            // Level specific input
            if ('level' === $name) {
                $builder->add($name, ChoiceType::class, [
                    'required' => true,
                    'choices' => array_flip(Recipe::$levels),
                ]);
            // Input specific to the field name
            } elseif (array_key_exists($name, self::NAMES)) {
                $builder->add($name, self::NAMES[$name], [
                    'required' => false,
                ]);
            } elseif (array_key_exists($type->getName(), self::TYPES)) {
                $builder->add($name, self::TYPES[$type->getName()], [
                    'required' => !$type->allowsNull() && 'bool' !== $type->getName(),
                ]);
            } else {
                throw new \RuntimeException(sprintf($this->translator->trans('Unable to find the field associated with the type %s in %s::%s'), $type->getName(), $data::class, $name));
            }
        }
    }
}
