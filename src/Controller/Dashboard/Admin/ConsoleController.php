<?php

namespace App\Controller\Dashboard\Admin;

use App\Entity\Traits\HasRoles;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%website_dashboard_path%/admin/console', name: 'dashboard_admin_console_')]
#[IsGranted(HasRoles::ADMINAPPLICATION)]
class ConsoleController extends AdminBaseController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('dashboard/admin/console/index.html.twig');
    }

    #[Route(path: '/execute-command/{command}/{optionKey}/{optionValue}', name: 'execute_command', methods: ['GET'])]
    public function executeCommand($command, $optionKey, $optionValue, KernelInterface $kernel, TranslatorInterface $translator): JsonResponse
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => $command,
            $optionKey => $optionValue,
        ]);

        $output = new NullOutput();
        $application->run($input, $output);

        return new JsonResponse($translator->trans('Successfully executed the command').' '.$command.' '.$optionKey.'='.$optionValue);
    }
}
