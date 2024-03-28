<?php

namespace App\Form;

use App\Entity\Setting\Currency;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

class CurrencyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ccy', TextType::class, [
                'label' => t('CCY'),
                // 'purify_html' => true,
                'required' => true,
                'help' => t('Please refer to this following list and use the Code column: https://en.wikipedia.org/wiki/ISO_4217'),
            ])
            ->add('symbol', TextType::class, [
                'label' => t('Currency symbol'),
                // 'purify_html' => true,
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Currency::class,
        ]);
    }
}
