<?php

namespace App\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Validator
{
    public static function validate($object, ExecutionContextInterface $context, $payload): void
    {
        global $kernel;
        $defaultlocalecheck = false;

        foreach ($object->getTranslations() as $translation) {
            if ($translation->getLocale() === $kernel->getContainer()->getParameter('locale')) {
                $defaultlocalecheck = true;
                break;
            }
        }

        if (!$defaultlocalecheck) {
            $context->buildViolation('You must set the default locale at least for the translation fields')
                    ->atPath('translations')
                    ->addViolation();
        }
    }

    public static function validateRecipe($object, ExecutionContextInterface $context, $payload): void
    {
        global $kernel;
        $defaultlocalecheck = false;

        foreach ($object->getTranslations() as $translation) {
            if ($translation->getLocale() === $kernel->getContainer()->getParameter('locale')) {
                $defaultlocalecheck = true;
                break;
            }
        }

        if (!$defaultlocalecheck) {
            $context->buildViolation('You must set the default locale at least for the translation fields')
                    ->atPath('translations')
                    ->addViolation();
        }

        foreach ($object->getRecipedates() as $indexRecipeDate => $recipeDate) {
            foreach ($recipeDate->getSubscriptions() as $indexDateSubscription => $recipeDateSubscription) {
                if (!$recipeDateSubscription->getFree() && !$recipeDateSubscription->getPrice()) {
                    $context->buildViolation('This value should not be blank.')
                            ->atPath('recipedates['.$indexRecipeDate.'].subscriptions['.$indexDateSubscription.'].price')
                            ->addViolation();
                }

                if (true === $recipeDate->getHasSeatingPlan() && null !== $recipeDate->getSeatingPlan() && 0 == count($recipeDateSubscription->getSeatingPlanSections())) {
                    $context->buildViolation('This value should not be blank.')
                            ->atPath('recipedates['.$indexRecipeDate.'].subscriptions['.$indexDateSubscription.'].seatingPlanSections')
                            ->addViolation();
                }
            }
        }

        foreach ($object->getRecipedates() as $indexRecipeDate => $recipeDate) {
            foreach ($recipeDate->getSubscriptions() as $indexDateSubscription => $recipeDateSubscription) {
                foreach ($recipeDate->getSubscriptions() as $indexDateSubscriptionToCheck => $recipeDateSubscriptionToCheck) {
                    if ($recipeDateSubscription != $recipeDateSubscriptionToCheck) {
                        foreach ($recipeDateSubscription->getSeatingPlanSections() as $sectionName) {
                            if (in_array($sectionName, $recipeDateSubscriptionToCheck->getSeatingPlanSections())) {
                                $context->buildViolation('Section "'.$sectionName.'" has been already assigned')
                                        ->atPath('recipedates['.$indexRecipeDate.'].subscriptions['.$indexDateSubscription.'].seatingPlanSections')
                                        ->addViolation();
                            }
                        }
                    }
                }
            }
        }
    }
}
