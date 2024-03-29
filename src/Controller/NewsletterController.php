<?php

namespace App\Controller;

use App\Service\SettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class NewsletterController extends AbstractController
{
    #[Route('/newsletter-subscribe', name: 'newsletter_subscribe', methods: ['GET'])]
    public function subscribe(
        Request $request,
        SettingService $settingService,
        EventDispatcherInterface $eventdispatcher,
        TranslatorInterface $translator
    ): JsonResponse {
        /*
        $subscriber = new Subscriber($request->request->get('email'), [], [
            'language' => $request->getLocale(),
        ]);

        try {
            $eventdispatcher->dispatch(
                SubscriberEvent::EVENT_SUBSCRIBE, new SubscriberEvent($settingService->getSettings('mailchimp_list_id'), $subscriber)
            );
        } catch (\Exception $e) {
            return new JsonResponse(['danger' => $translator->trans('An error has occured')]);
        }
        */

        return new JsonResponse(['success' => $translator->trans('You have successfully subscribed to our newsletter')]);
    }
}
