<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigMarkdownContentExtension extends AbstractExtension
{
    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('html_excerpt', $this->htmlExcerpt(...)),
            new TwigFilter('html_markdown', $this->htmlMarkdown(...), ['is_safe' => ['html']]),
            new TwigFilter('html_markdown_excerpt', $this->htmlMarkdownExcerpt(...), ['is_safe' => ['html']]),
            new TwigFilter('html_markdown_untrusted', $this->htmlMarkdownUntrusted(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * Returns an htmlExcerpt from a text.
     */
    public function htmlExcerpt(?string $content, int $characterLimit = 135): string
    {
        if (null === $content) {
            return '';
        }

        if (mb_strlen($content) <= $characterLimit) {
            return $content;
        }

        $lastSpace = mb_strpos($content, ' ', $characterLimit);
        if (false === $lastSpace) {
            return $content;
        }

        return mb_substr($content, 0, $lastSpace).'...';
    }

    /**
     * Converts htmlMarkdown content to HTML.
     */
    public function htmlMarkdown(?string $content): string
    {
        if (null === $content) {
            return '';
        }

        $content = (new \Parsedown())->setBreaksEnabled(true)->setSafeMode(false)->text($content);

        // We replace YouTube links with an embed
        $content = (string) preg_replace(
            '/<p><a href\="(http|https):\/\/www.youtube.com\/watch\?v=([^\""]+)">[^<]*<\/a><\/p>/',
            '<iframe
                width="560"
                height="315"
                src="//www.youtube-nocookie.com/embed/$2"
                frameborder="0"
                allowfullscreen=""
            ></iframe>',
            (string) $content
        );

        // Spoiler tag
        $content = (string) preg_replace(
            '/<p>!!<\/p>/',
            '<spoiler-box>',
            (string) $content
        );

        $content = (string) preg_replace(
            '/<p>\/!!<\/p>/',
            '</spoiler-box>',
            (string) $content
        );

        // We add links on the numbers representing a timestamp "00:01"
        $content = preg_replace_callback('/((\d{2}:){1,2}\d{2}) ([^<]*)/', function ($matches) {
            $times = array_reverse(explode(':', $matches[1]));
            $title = $matches[3];
            $timecode = (int) ($times[2] ?? 0) * 60 * 60 + (int) $times[1] * 60 + (int) $times[0];

            return "<a href=\"#t{$timecode}\">{$matches[1]}</a> $title";
        }, $content) ?: $content;

        return $content;
    }

    public function htmlMarkdownExcerpt(?string $content, int $characterLimit = 135): string
    {
        return $this->htmlExcerpt(strip_tags($this->htmlMarkdown($content)), $characterLimit);
    }

    public function htmlMarkdownUntrusted(?string $content): string
    {
        $content = strip_tags((new \Parsedown())
            ->setSafeMode(true)
            ->setBreaksEnabled(true)
            ->text($content), '<p><pre><code><ul><ol><li><h4><h3><h5><a><strong><br><em>')
        ;

        $content = str_replace('<a href="http', '<a target="_blank" rel="noreferrer nofollow" href="http', $content);
        $content = str_replace('<a href="//', '<a target="_blank" rel="noreferrer nofollow" href="http', $content);

        return $content;
    }
}
