<?php

declare(strict_types=1);

namespace App\Controller\Dashboard\Shared;

use App\Entity\User;
use App\Entity\Traits\HasRoles;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @method User getUser()
 */
#[Route(path: '/%website_dashboard_path%/account/my-subscriptions', name: 'dashboard_account_')]
#[IsGranted(HasRoles::DEFAULT)]
class AccountSubscriptionController extends AbstractController
{
    public function __construct(
        /*private readonly StripeApi $api*/
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/', name: 'subscription', methods: ['POST'])]
    public function accountSubscription(): Response
    {
        $user = $this->getUser();

        $redirectUrl = $this->generateUrl('dashboard_account_invoice_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        if (null === $user->getStripeId()) {
            $this->addFlash('danger', $this->translator->trans("You do not have an active subscription."));

            return $this->redirect($redirectUrl);
        }

        //return $this->redirect($this->api->getBillingUrl($user, $redirectUrl));
        return $this->redirect($redirectUrl);
    }
}