<?php

namespace App\Infrastructural\Normalizer;

use App\Entity\User;
use App\Infrastructural\Normalizer\Path\Encoder;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

class UserPathNormalizer extends Normalizer
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if ($object instanceof User) {
            return [
                'path' => 'user_profil',
                'params' => ['slug' => $object->getSlug()],
            ];
        }

        throw new \RuntimeException("Can't normalize path");
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof User && Encoder::FORMAT === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => true,
        ];
    }
}
