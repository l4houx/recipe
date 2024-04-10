<?php

namespace App\Infrastructural\Normalizer;

use App\Entity\Revise;
use App\Infrastructural\Normalizer\Path\Encoder;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

class ReviseNormalizer extends Normalizer
{
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        if ($object instanceof Revise) {
            return [
                'path' => 'dashboard_revise_index',
            ];
        }

        throw new \RuntimeException("Can't normalize path");
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Revise && Encoder::FORMAT === $format;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => true,
        ];
    }
}
