<?php

namespace App\Twig;

use App\Helper\DateTimeHelper;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Twig\Extension\AbstractExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\TwigFilter;

class TwigDateTimeExtension extends AbstractExtension
{
    public function __construct(
        protected IntlExtension $intlExtension,
        protected Environment $environment
    ) {
        // code...
    }

    /**
     * @return array<TwigFilter>
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('duration', $this->duration(...)),
            new TwigFilter('ago', $this->ago(...), ['is_safe' => ['html']]),
            new TwigFilter('countdown', $this->countdown(...), ['is_safe' => ['html']]),
            new TwigFilter('duration_short', $this->shortDuration(...), ['is_safe' => ['html']]),
            new TwigFilter('localizeddate', $this->localizedDate(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generates a duration in “30 min” format.
     */
    public function duration(int $duration): string
    {
        return DateTimeHelper::duration($duration);
    }

    /**
     * Generates a duration in short hh:mm:ss format.
     */
    public function shortDuration(int $duration): string
    {
        $minutes = floor($duration / 60);
        $seconds = $duration - $minutes * 60;
        $times = [$minutes, $seconds];

        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $minutes = $minutes - ($hours * 60);
            $times = [$hours, $minutes, $seconds];
        }

        return implode(':', array_map(
            fn (int|float $duration) => str_pad((string) $duration, 2, '0', \STR_PAD_LEFT),
            $times
        ));
    }

    /**
     * Generates a date in "There is" format using a CustomElement.
     */
    public function ago(\DateTimeInterface $date, string $prefix = ''): string
    {
        $prefixAttribute = !empty($prefix) ? " prefix=\"{$prefix}\"" : '';

        return "<time-ago time=\"{$date->getTimestamp()}\"$prefixAttribute></time-ago>";
    }

    public function countdown(\DateTimeInterface $date): string
    {
        return "<time-countdown time=\"{$date->getTimestamp()}\"></time-countdown>";
    }

    /**
     * @param \DateTimeInterface|string|null $dateTime A date or null to use the current time
     * @param \DateTimeZone|string|false|null $timezone The target timezone, null to use the default, false to leave unchanged
     * 
     * @throws \Twig\Error\RuntimeError
     */
    public function localizedDate(
        \DateTimeInterface|string|null $dateTime,
        ?string $dateFormat = 'long',
        ?string $timeFormat = 'short',
        ?string $locale = null,
        $timezone = null,
    ): string {
        return $this->intlExtension->formatDateTime(
            $this->environment, $dateTime, dateFormat: $dateFormat, timeFormat: $timeFormat, locale: $locale, timezone: $timezone
        );
    }
}
