<?php

namespace App\Controller;

use App\Entity\User;
use App\Infrastructural\Messenger\Message\ServiceMethodMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @method User|null getUser()
 */
abstract class BaseController extends AbstractController
{
    /**
     * Displays the list of errors as a flash message.
     */
    protected function flashErrors(FormInterface $form): void
    {
        /** @var FormError[] $errors */
        $errors = $form->getErrors();
        $messages = [];

        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }

        $this->addFlash('danger', implode("\n", $messages));
    }

    protected function getUserOrThrow(): User
    {
        $user = $this->getUser();

        if (!($user instanceof User)) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    /**
     * Redirects the user to the previous page or route in case of fallback.
     */
    protected function redirectBack(string $route, array $params = []): RedirectResponse
    {
        /** @var RequestStack $stack */
        $stack = $this->getParameter('request_stack');
        $request = $stack->getCurrentRequest();

        if ($request && $request->server->get('HTTP_REFERER')) {
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        return $this->redirectToRoute($route, $params);
    }

    /**
     * Launches a service method asynchronously.
     */
    protected function dispatchMethod(
        MessageBusInterface $messageBus,
        string $service,
        string $method,
        array $params = []
    ): Envelope {
        return $messageBus->dispatch(new ServiceMethodMessage($service, $method, $params), []);
    }
}
