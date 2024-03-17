<?php

declare(strict_types=1);

namespace App\EventSubscriber\Setting;

use App\Entity\Setting\Setting;
use App\Entity\Traits\HasRoles;
use App\Service\SettingService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly Environment $templating,
        private readonly SettingService $settingService
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        try {
            if ('1' === $this->params->get('maintenance_mode') && !$this->security->isGranted(HasRoles::ADMINAPPLICATION)) {
                $event->setController(
                    function () {
                        /* @phpstan-ignore-next-line */
                        return new Response($this->templating->render('pages/maintenance-mode.html.twig', ['customMessage' => $this->settingService->getValue(Setting::MAINTENANCE_MODE_CUSTOM_MESSAGE)], Response::HTTP_SERVICE_UNAVAILABLE));
                    }
                );
            }
        } catch (AuthenticationCredentialsNotFoundException $e) {
        }
    }
}
