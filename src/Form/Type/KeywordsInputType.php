<?php

namespace App\Form\Type;

use App\Form\DataTransformer\KeywordArrayToStringTransformer;
use App\Repository\KeywordRepository;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class KeywordsInputType extends AbstractType
{
    public function __construct(
        private readonly KeywordRepository $keywords
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true)
            ->addModelTransformer(new KeywordArrayToStringTransformer($this->keywords), true)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['keywords'] = $this->keywords->findAll();
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
