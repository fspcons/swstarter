<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Film;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\FilmRepositoryInterface;
use App\Domain\ValueObjects\SearchQuery;
use App\Infrastructure\ExternalServices\SwapiClient;

final class SwapiFilmRepository implements FilmRepositoryInterface
{
    public function __construct(
        private SwapiClient $client,
    ) {}

    /**
     * @return Film[]
     */
    public function searchByTitle(SearchQuery $query): array
    {
        $data = $this->client->searchFilms($query->value);
        $results = $data['result'] ?? [];

        if (! is_array($results)) {
            return [];
        }

        return array_map(
            fn (array $item) => Film::fromSwapiProperties(
                (string) ($item['uid'] ?? ''),
                $item['properties'] ?? []
            ),
            $results
        );
    }

    public function findById(string $id): Film
    {
        $data = $this->client->getFilm($id);
        $result = $data['result'] ?? null;

        if (! $result || ! isset($result['properties'])) {
            throw EntityNotFoundException::film($id);
        }

        return Film::fromSwapiProperties(
            (string) ($result['uid'] ?? $id),
            $result['properties']
        );
    }
}
