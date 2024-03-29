<?php

namespace App\Infrastructural\KnpUOAuth\Normalizer;

use App\Infrastructural\Normalizer\Normalizer;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;

class GithubNormalizer extends Normalizer
{
    /**
     * @param GithubResourceOwner $object
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return [
            'email' => $object->getEmail(),
            'github_id' => $object->getId(),
            'type' => 'Github',
            'username' => $object->getNickname(),
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GithubResourceOwner;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            NormalizableInterface::class => true,
        ];
    }
}
