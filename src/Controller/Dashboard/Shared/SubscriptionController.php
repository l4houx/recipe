<?php

declare(strict_types=1);

namespace App\Controller\Dashboard\Shared;

use App\Entity\User;
use App\Entity\Traits\HasRoles;
use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Infrastructural\Payment\Stripe\StripeApi;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @method User getUser()
 */
#[Route(path: '/%website_dashboard_path%/my-subscriptions', name: 'dashboard_subscription_')]
#[IsGranted(HasRoles::DEFAULT)]
class SubscriptionController extends BaseController
{
    public function __construct(
        private readonly StripeApi $api,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['POST'])]
    public function index(): Response
    {
        $user = $this->getUser();

        $redirectUrl = $this->generateUrl('dashboard_invoice_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        if (null === $user->getStripeId()) {
            $this->addFlash('danger', $this->translator->trans('You do not have an active subscription.'));

            return $this->redirect($redirectUrl);
        }

        return $this->redirect($this->api->getBillingUrl($user, $redirectUrl));
    }
}
