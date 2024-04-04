<?php

namespace App\Form;

use App\Entity\User;
use function Symfony\Component\Translation\t;
use App\Service\SettingService;
use App\Entity\RecipeSubscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecipeSubscriptionFormType extends AbstractType {

    /** @var User */
    private User $user;
    private array $seatingPlansSections = [];

    public function __construct(
        private readonly SettingService $settingService, 
        private readonly TokenStorageInterface $tokenStorage
    ) {
        $this->user = $tokenStorage->getToken()->getUser();

        foreach ($this->user->getRestaurant()->getVenues() as $venue) {
            if (count($venue->getSeatingPlans()) > 0) {
                foreach ($venue->getSeatingPlans() as $seatingPlan) {
                    $this->seatingPlansSections[$seatingPlan->getVenue()->getName() . ' - ' . $seatingPlan->getName()] = $seatingPlan->getSectionsNamesArray();
                }
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('isActive', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Enable sales for this subscription ? :'),
                'choices' => ['Yes' => true, 'No' => false],
                'attr' => ['class' => 'is-subscription-active'],
                'label_attr' => ['class' => 'radio-custom radio-inline']
            ])
            ->add('name', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Subscription name :'),
                'help' => 'Early bird, General admission, VIP...'
            ])
            ->add('description', TextareaType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Subscription description :'),
                'help' => t('Tell your attendees more about this subscription type')
            ])
            ->add('isFree', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'label' => t('Is this subscription free ? :'),
                'choices' => ['No' => false, 'Yes' => true],
                'attr' => ['class' => 'is-subscription-free-radio'],
                'label_attr' => ['class' => 'radio-custom radio-inline']
            ])
            ->add('price', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Price :'),
                'attr' => ['class' => 'touchspin-decimal event-date-subscription-price', 'data-min' => '0', "data-max" => '100000000']
            ])
            ->add('promotionalPrice', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Promotional price :'),
                'help' => t('Set a price lesser than than the original price to indicate a promotion (this price will be the SALE price)'),
                'attr' => ['class' => 'touchspin-decimal event-date-subscription-promotionalprice', 'data-min' => '0', "data-max" => '100000000']
            ])
            ->add('seatingPlanSections', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'expanded' => false,
                'label' => t('Seating plan sections :'),
                'choices' => $this->seatingPlansSections,
                'choice_value' => function ($sectionName) {
                    return $sectionName;
                },
                'help' => '<ul class="list-unstyled"><li>Press CTRL to select multiple sections, press SHIFT to select all sections</li><li>A section can only be assigned to one subscription in an event date</li></ul>',
                'help_html' => true,
                'attr' => ['class' => 'event-date-subscription-seating-plan-sections '],
            ])
            ->add('quantity', TextType::class, [
                'purify_html' => true,
                'required' => true,
                'label' => t('Quantity :'),
                'attr' => ['class' => 'touchspin-integer event-date-subscription-quantity', 'data-min' => '1', "data-max" => '1000000']
            ])
            ->add('subscriptionsperattendee', TextType::class, [
                'purify_html' => true,
                'required' => false,
                'label' => t('Subscriptions per attendee :'),
                'help' => t('Set the number of subscriptions that an attendee can buy for this subscription type'),
                'attr' => ['class' => 'touchspin-integer', 'data-min' => '1', "data-max" => '1000000']
            ])
            ->add('salesstartdate', DateTimeType::class, [
                'required' => false,
                'label' => t('Sale starts On :'),
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'datetimepicker']
            ])
            ->add('salesenddate', DateTimeType::class, [
                'required' => false,
                'label' => t('Sale ends On :'),
                'widget' => 'single_text',
                'html5' => false,
                'attr' => ['class' => 'datetimepicker']
            ])
            ->add('position', HiddenType::class, [
                'attr' => [
                    'class' => 'event-date-subscription-position']
            ])
            // Set automatically on entity creation (generation function on entity class),
            // added here as a trick to identity the event subscription
            // fieldset to set the data-min attribute as the current subscription sales number
            ->add('reference', HiddenType::class, [
                'attr' => [
                    'class' => 'event-date-subscription-reference']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => RecipeSubscription::class,
            'validation_groups' => ['create', 'update']
        ]);
    }
}
