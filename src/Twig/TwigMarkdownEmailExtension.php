<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigMarkdownEmailExtension extends AbstractExtension
{
    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('html_markdown_email', $this->htmlMarkdownEmail(...), [
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFilter('html_text_email', $this->htmlFormatText(...)),
        ];
    }

    /**
     * Converts markdown content to HTML.
     */
    public function htmlMarkdownEmail(array $context, string $content): string
    {
        if (($context['format'] ?? 'text') === 'text') {
            return $content;
        }
        $content = preg_replace('/^(^ {2,})(\S+[ \S]*)$/m', '${2}', $content);
        $content = (new \Parsedown())->setSafeMode(false)->text($content);

        return $content;
    }

    public function htmlFormatText(string $content): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/^(^ {2,})(\S+[ \S]*)$/m', '${2}', $content) ?: '';
        $content = preg_replace("/([\r\n] *){3,}/", "\n\n", $content) ?: '';

        return $content;
    }
}
