<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Person;
use App\Domain\Exceptions\EntityNotFoundException;
use App\Domain\Repositories\PersonRepositoryInterface;
use App\Domain\ValueObjects\SearchQuery;
use App\Infrastructure\ExternalServices\SwapiClient;

final class SwapiPersonRepository implements PersonRepositoryInterface
{
    public function __construct(
        private SwapiClient $client,
    ) {}

    /**
     * @return Person[]
     */
    public function searchByName(SearchQuery $query): array
    {
        $data = $this->client->searchPeople($query->value);
        $results = $data['result'] ?? [];

        if (! is_array($results)) {
            return [];
        }

        return array_map(
            fn (array $item) => Person::fromSwapiProperties(
                (string) ($item['uid'] ?? ''),
                $item['properties'] ?? []
            ),
            $results
        );
    }

    public function findById(string $id): Person
    {
        $data = $this->client->getPerson($id);
        $result = $data['result'] ?? null;

        if (! $result || ! isset($result['properties'])) {
            throw EntityNotFoundException::person($id);
        }

        return Person::fromSwapiProperties(
            (string) ($result['uid'] ?? $id),
            $result['properties']
        );
    }
}
