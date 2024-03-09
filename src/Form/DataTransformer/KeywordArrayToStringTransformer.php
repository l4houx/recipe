<?php

namespace App\Form\DataTransformer;

use App\Entity\Keyword;
use App\Repository\KeywordRepository;
use function Symfony\Component\String\u;
use Symfony\Component\Form\DataTransformerInterface;

class KeywordArrayToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly KeywordRepository $keywords
    ) {
    }

    public function transform($keywords): string
    {
        return implode(',', $keywords);
    }

    /**
     * @phpstan-param string|null $string
     *
     * @phpstan-return array<int, Keyword>
     */
    public function reverseTransform($string): array
    {
        if (null === $string || u($string)->isEmpty()) {
            return [];
        }

        $names = array_filter(array_unique($this->trim(u($string)->split(','))));

        /** @var Keyword[] $keywords */
        $keywords = $this->keywords->findBy([
            'name' => $names,
        ]);

        $newNames = array_diff($names, $keywords);

        foreach ($newNames as $name) {
            $keywords[] = new Keyword($name);
        }

        return $keywords;
    }

    /**
     * @param string[] $strings
     *
     * @return string[]
     */
    private function trim(array $strings): array
    {
        $result = [];

        foreach ($strings as $string) {
            $result[] = trim($string);
        }

        return $result;
    }
}
