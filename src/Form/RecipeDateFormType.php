<?php

namespace App\Form;

use App\Entity\PointOfSale;
use App\Entity\RecipeDate;
use App\Entity\Scanner;
use App\Entity\User;
use App\Entity\Venue;
use App\Entity\VenueSeatingPlan;
use App\Service\SettingService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use function Symfony\Component\Translation\t;

class RecipeDateFormType extends AbstractType
{
    /** @var User */
    private User $user;

    public function __construct(
        private readonly SettingService $settingService,
        private readonly TokenStorageInterface $tokenStorage
    ) {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isActive', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Enable sales for this recipe date ? :'),
                'choices' => ['Yes' => true, 'No' => false],
                'attr' => ['class' => 'is-recipe-date-active'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
                'help' => t('Enabling sales for an recipe date does not affect the subscriptions individual sale status'),
            ])
            ->add('startdate', DateTimeType::class, [
                'required' => true,
                'label' => t('Starts On :'),
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'datetimepicker'],
            ])
            ->add('enddate', DateTimeType::class, [
                'required' => false,
                'label' => t('Ends On :'),
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'datetimepicker'],
            ])
            ->add('isOnline', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Is this recipe date online ? :'),
                'choices' => ['No' => false, 'Yes' => true],
                'attr' => ['class' => 'is-recipe-date-online'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            ->add('hasSeatingPlan', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Does this recipe date has a seating plan ? :'),
                'choices' => ['No' => false, 'Yes' => true],
                'attr' => ['class' => 'recipe-date-has-seating-plan'],
                'label_attr' => ['class' => 'radio-custom radio-inline'],
            ])
            ->add('venue', EntityType::class, [
                'required' => false,
                'class' => Venue::class,
                'choice_label' => 'name',
                'label' => t('Venue :'),
                'attr' => ['class' => 'recipe-date-venue'],
                'query_builder' => function () {
                    return $this->settingService->getVenues(['restaurant' => $this->user->getRestaurant()->getSlug()]);
                },
            ])
            ->add('seatingPlan', EntityType::class, [
                'required' => false,
                'class' => VenueSeatingPlan::class,
                'choice_label' => function ($seatingPlan) {
                    return $seatingPlan->getVenue()->getName().' - '.$seatingPlan->getName();
                },
                'label' => t('Seating plan :'),
                'attr' => ['class' => 'recipe-date-seating-plan'],
                'query_builder' => function () {
                    return $this->settingService->getVenuesSeatingPlans(['restaurant' => $this->user->getRestaurant()->getSlug()]);
                },
            ])
            ->add('scanners', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'class' => Scanner::class,
                'choice_label' => 'name',
                'label' => t('Scanners :'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                    ->where('s.restaurant = :restaurant')
                    ->leftJoin('s.user', 'user')
                    ->andWhere('user.isVerified = :isVerified')
                    ->setParameter('restaurant', $this->user->getRestaurant())
                    ->setParameter('isVerified', true)
                    ;
                },
                'attr' => ['class' => 'select2'],
            ])
            ->add('pointofsales', EntityType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'class' => PointOfSale::class,
                'choice_label' => 'name',
                'label' => t('Points of sale :'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                    ->where('p.restaurant = :restaurant')
                    ->leftJoin('p.user', 'user')
                    ->andWhere('user.isVerified = :isVerified')
                    ->setParameter('restaurant', $this->user->getRestaurant())
                    ->setParameter('isVerified', true)
                    ;
                },
                'attr' => ['class' => 'select2'],
            ])
            ->add('subscriptions', CollectionType::class, [
                'label' => t('Recipe subscriptions :'),
                'entry_type' => RecipeSubscriptionFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'prototype_name' => '__recipesubscription__',
                'required' => true,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-collection recipesubscriptions-collection manual-init',
                ],
            ])
            // Set automatically on entity creation (generation function on entity class),
            // added here as a trick to identity the recipe date on the form to disable the wrapping
            // fieldset when payout request is pending on approved
            ->add('reference', HiddenType::class, [
                'attr' => [
                    'class' => 'recipe-date-reference'],
            ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeDate::class,
            'validation_groups' => ['create', 'update'],
        ]);
    }
}
