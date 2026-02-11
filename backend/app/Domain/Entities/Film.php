<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\SwapiUrlParser;

final class Film
{
    private function __construct(
        private string $uid,
        private string $title,
        private string $openingCrawl,
        private array $characterUrls,
    ) {}

    /**
     * Factory method that maps only the SWAPI properties we care about,
     * ignoring everything else (planets, species, starships, vehicles, etc.).
     */
    public static function fromSwapiProperties(string $uid, array $props): self
    {
        return new self(
            uid: $uid,
            title: $props['title'] ?? 'Unknown',
            openingCrawl: $props['opening_crawl'] ?? '',
            characterUrls: $props['characters'] ?? [],
        );
    }

    public function uid(): string
    {
        return $this->uid;
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * Extract numeric character IDs from SWAPI URLs.
     */
    public function characterIds(): array
    {
        return SwapiUrlParser::extractIds($this->characterUrls);
    }

    /**
     * Returns a slim representation for search result listings.
     */
    public function toSearchResult(): array
    {
        return [
            'uid' => $this->uid,
            'title' => $this->title,
        ];
    }

    /**
     * Returns all detail fields for the details page.
     * Character IDs are included so the use case can resolve them to names.
     */
    public function toDetails(): array
    {
        return [
            'uid' => $this->uid,
            'title' => $this->title,
            'opening_crawl' => $this->openingCrawl,
            'character_ids' => $this->characterIds(),
        ];
    }
}
