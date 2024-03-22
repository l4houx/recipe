<?php

namespace App\Twig;

use App\Infrastructural\Uploads\Picture\ResizerPicture;
use App\Validator\AttachmentNoExist;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class TwigPictureExtension extends AbstractExtension
{
    public function __construct(
        private readonly ResizerPicture $resizerPicture,
        private readonly UploaderHelper $helper
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pathUploads', $this->pathUploads(...)),
            new TwigFunction('urlImage', $this->urlImage(...)),
            new TwigFunction('urlRawImage', $this->urlRawImage(...)),
            new TwigFunction('image', $this->imageTag(...), ['is_safe' => ['html']]),
        ];
    }

    public function pathUploads(string $path): string
    {
        return '/uploads/'.trim($path, '/');
    }

    public function urlImage(?object $entity, ?int $width = null, ?int $height = null): ?string
    {
        if (null === $entity || $entity instanceof AttachmentNoExist) {
            return null;
        }

        $path = $this->helper->asset($entity);

        if (null === $path) {
            return null;
        }

        if ('jpg' !== pathinfo($path, \PATHINFO_EXTENSION)) {
            return $path;
        }

        return $this->resizerPicture->resize($this->helper->asset($entity), $width, $height);
    }

    public function urlRawImage(?object $entity): string
    {
        if (null === $entity || $entity instanceof AttachmentNoExist) {
            return '';
        }

        return $this->helper->asset($entity) ?: '';
    }

    public function imageTag(?object $entity, ?int $width = null, ?int $height = null): ?string
    {
        $url = $this->urlImage($entity, $width, $height);
        if (null !== $url) {
            return "<img src=\"{$url}\" width=\"{$width}\" height=\"{$height}\"/>";
        }

        return null;
    }
}
