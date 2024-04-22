<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Level;
use App\Entity\Ticket;
use App\Entity\Application;
use App\Entity\Traits\HasRoles;
use Symfony\Component\Form\AbstractType;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use function Symfony\Component\Translation\t;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TicketFormType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('application', EntityType::class, [
                'class' => Application::class,
                'choice_label' => 'name',
                'query_builder' => function (ApplicationRepository $repo) use ($user) {
                    if ($this->security->isGranted(HasRoles::ADMIN)) {
                        return $repo->createQueryBuilder('a');
                    }
                    return $repo->createQueryBuilder('a')
                        ->where('a.user = :user')
                        ->setParameter('user', $user)
                    ;
                }
            ])
            ->add('level', EntityType::class, [
                'class' => Level::class,
                'choice_label' => 'name'
            ])
            ->add('subject', TextType::class, [
                'label' => t('Subject'),
            ])
            ->add('content', TextareaType::class, [
                'label' => t("Content :"),
                'purify_html' => true,
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => '', 'rows' => 6],
                'help' => t(''),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
