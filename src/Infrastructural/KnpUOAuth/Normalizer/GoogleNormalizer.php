<?php

namespace App\Infrastructural\KnpUOAuth\Normalizer;

use App\Infrastructural\Normalizer\Normalizer;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

class GoogleNormalizer extends Normalizer
{
    /**
     * @param GoogleUser $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'email' => $object->getEmail(),
            'google_id' => $object->getId(),
            'type' => 'Google',
            'username' => $object->getName(),
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GoogleUser;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => true,
        ];
    }
}
