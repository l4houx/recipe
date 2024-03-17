<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use function Symfony\Component\Translation\t;

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => t('Comment :'),
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
                'help' => t('Comments that do not comply with our code of conduct will be moderated.'),
            ])
            /*
            ->add('isRGPD', CheckboxType::class, [
                'label' => "Oui, j'accepte la politique de confidentialité",
                'data' => true, // Default checked
                'constraints' => [
                    new NotBlank(['message' => "S'il vous plaît, ne laissez pas le rgpd vide!"]),
                ],
            ])
            
            ->add('save', SubmitType::class, [
                'label' => 'Laisse un commentaire',
                'validate' => false,
                'attr' => ['class' => 'btn btn-primary'],
            ])
            */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
