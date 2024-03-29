<?php

namespace App\Infrastructural\KnpUOAuth\Normalizer;

use App\Infrastructural\Normalizer\Normalizer;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

class FacebookNormalizer extends Normalizer
{
    /**
     * @param FacebookUser $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'email' => $object->getEmail(),
            'facebook_id' => $object->getId(),
            'type' => 'Facebook',
            'username' => $object->getName(),
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FacebookUser;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => true,
        ];
    }
}
