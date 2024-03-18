<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class TextToUrlDataTransformer implements DataTransformerInterface
{
    /**
     * @return mixed|string
     */
    public function transform(mixed $url): mixed
    {
        if (null === $url) {
            return '';
        }

        $newFormatUrl = explode('https://', $url);

        if (isset($newUrl[1])) {
            return $newUrl[1];
        }

        return $newFormatUrl[1];
    }

    /**
     * @return mixed|string|null
     */
    public function reverseTransform(mixed $string): mixed
    {
        if (null === $string) {
            return null;
        }

        return 'https://'.$string;
    }
}
