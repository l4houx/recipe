<?php

namespace App\Infrastructural\MessageHandler;

use Symfony\Component\Process\Process;
use App\Infrastructural\Message\RecipePDFMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

#[AsMessageHandler]
final class RecipePDFMessageHandler
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/pdfs')]
        private readonly string $path,
        private readonly UrlGeneratorInterface $urlGeneratorInterface
    ) {
        # code...
    }

    public function __invoke(RecipePDFMessage $message): void
    {
        $process = new Process([
            'curl',
            '--request',
            'POST',
            'http://localhost:3001/forms/chromium/convert/url',
            '--form',
            'url=' . $this->urlGeneratorInterface->generate('recipe_show', ['id' => $message->id], UrlGeneratorInterface::ABSOLUTE_URL),
            '-o',
            $this->path . '/' . $message->id . '.pdf'
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
