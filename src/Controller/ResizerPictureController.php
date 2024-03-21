<?php

namespace App\Controller;

use App\Infrastructural\Uploads\Picture\ResponseFactory;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResizerPictureController extends AbstractController
{
    private readonly string $cachePath;
    private readonly string $resizeKey;
    private readonly string $publicPath;

    public function __construct(
        ParameterBagInterface $param
    ) {
        $projectDir = $param->get('kernel.project_dir');
        $resizeKey = $param->get('resize_picture_key');

        if (!\is_string($projectDir)) {
            throw new \RuntimeException('Parameter kernel.project_dir is not a string');
        }

        if (!\is_string($resizeKey)) {
            throw new \RuntimeException('Parameter resize_picture_key is not a string');
        }

        $this->cachePath = $projectDir.'/var/images';
        $this->publicPath = $projectDir.'/public';
        $this->resizeKey = $resizeKey;
    }

    #[Route(
        path: '/picture/resize/{width}/{height}/{path}',
        name: 'picture_resizer',
        methods: ['GET'],
        requirements: ['width' => '\d+', 'height' => '\d+', 'path' => '.+']
    )]
    public function resizerPicture(Request $request, TranslatorInterface $translator, int $width, int $height, string $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->publicPath,
            'cache' => $this->cachePath,
            'driver' => 'imagick',
            'response' => new ResponseFactory(),
            'defaults' => [
                'q' => 75,
                'fm' => 'jpg',
                'fit' => 'crop',
            ],
        ]);

        [$url] = explode('?', $request->getRequestUri());

        try {
            SignatureFactory::create($this->resizeKey)->validateRequest($url, ['s' => $request->get('s')]);

            return $server->getImageResponse($path, ['w' => $width, 'h' => $height, 'fit' => 'crop']);
        } catch (SignatureException) {
            throw new HttpException(403, $translator->trans('Invalid signature'));
        }
    }

    #[Route(
        path: '/picture/convert/{path}',
        name: 'picture_jpg',
        methods: ['GET'],
        requirements: ['path' => '.+']
    )]
    public function convertPicture(Request $request, TranslatorInterface $translator, string $path): Response
    {
        $server = ServerFactory::create([
            'source' => $this->publicPath,
            'cache' => $this->cachePath,
            'driver' => 'imagick',
            'response' => new ResponseFactory(),
            'defaults' => [
                'q' => 75,
                'fm' => 'jpg',
                'fit' => 'crop',
            ],
        ]);

        [$url] = explode('?', $request->getRequestUri());

        try {
            SignatureFactory::create($this->resizeKey)->validateRequest($url, ['s' => $request->get('s')]);

            return $server->getImageResponse($path, ['fm' => 'jpg']);
        } catch (SignatureException) {
            throw new HttpException(403, $translator->trans('Invalid signature'));
        }
    }
}
