<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    /**
     * Generate error page content.
     */
    public function content(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/_content.html.twig');
    }

    /**
     * Simplify the display of errors in the test environment.
     */
    public function test(\Throwable $exception = null): Response
    {
        if (!$exception) {
            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $exception = FlattenException::createFromThrowable($exception);

        return new Response($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders());
    }
}
