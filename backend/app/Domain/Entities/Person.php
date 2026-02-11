<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\SwapiUrlParser;

final class Person
{
    private function __construct(
        private string $uid,
        private string $name,
        private string $birthYear,
        private string $gender,
        private string $eyeColor,
        private string $hairColor,
        private string $height,
        private string $mass,
        private array $filmUrls,
    ) {}

    /**
     * Factory method that maps only the SWAPI properties we care about,
     * ignoring everything else (species, vehicles, starships, etc.).
     */
    public static function fromSwapiProperties(string $uid, array $props): self
    {
        return new self(
            uid: $uid,
            name: $props['name'] ?? 'Unknown',
            birthYear: $props['birth_year'] ?? 'unknown',
            gender: $props['gender'] ?? 'unknown',
            eyeColor: $props['eye_color'] ?? 'unknown',
            hairColor: $props['hair_color'] ?? 'unknown',
            height: $props['height'] ?? 'unknown',
            mass: $props['mass'] ?? 'unknown',
            filmUrls: $props['films'] ?? [],
        );
    }

    public function uid(): string
    {
        return $this->uid;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Extract numeric film IDs from SWAPI URLs.
     */
    public function filmIds(): array
    {
        return SwapiUrlParser::extractIds($this->filmUrls);
    }

    /**
     * Returns a slim representation for search result listings.
     */
    public function toSearchResult(): array
    {
        return [
            'uid' => $this->uid,
            'name' => $this->name,
        ];
    }

    /**
     * Returns all detail fields for the details page.
     * Film IDs are included so the use case can resolve them to titles.
     */
    public function toDetails(): array
    {
        return [
            'uid' => $this->uid,
            'name' => $this->name,
            'birth_year' => $this->birthYear,
            'gender' => $this->gender,
            'eye_color' => $this->eyeColor,
            'hair_color' => $this->hairColor,
            'height' => $this->height,
            'mass' => $this->mass,
            'film_ids' => $this->filmIds(),
        ];
    }
}
