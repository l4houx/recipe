<?php

namespace App\Controller;

use App\Repository\SubscriptionRepository;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function subscription(SubscriptionRepository $subscriptionRepository): Response
    {
        $user = $this->getUserOrThrow();
        $subscription = $subscriptionRepository->findCurrentForUser($user);

        return $this->render('global/user-subscription.html.twig', compact('user', 'subscription'));
    }
}
