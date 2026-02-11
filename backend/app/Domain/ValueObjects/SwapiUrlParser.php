<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Extracts the numeric resource ID from SWAPI URLs.
 *
 * e.g. "https://www.swapi.tech/api/films/1" -> "1"
 */
final class SwapiUrlParser
{
    /**
     * @param  string[]  $urls
     * @return string[]
     */
    public static function extractIds(array $urls): array
    {
        return array_map(
            fn (string $url) => basename(rtrim($url, '/')),
            $urls
        );
    }
}
