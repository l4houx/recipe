<?php

namespace App\Infrastructural\Normalizer;

use App\Entity\Post;
use App\Infrastructural\Normalizer\Path\Encoder;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

class PostNormalizer extends Normalizer
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if ($object instanceof Post) {
            return [
                'path' => 'blog_article',
                'params' => ['slug' => $object->getSlug()],
            ];
        }

        throw new \RuntimeException("Can't normalize path");
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return ($data instanceof Post)
            && Encoder::FORMAT === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => true,
        ];
    }
}
