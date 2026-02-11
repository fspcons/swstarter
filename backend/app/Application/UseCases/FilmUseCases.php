<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Concerns\LogsSearchQuery;
use App\Domain\Repositories\FilmRepositoryInterface;
use App\Domain\Repositories\PersonRepositoryInterface;
use App\Domain\ValueObjects\SearchQuery;
use App\Infrastructure\Logging\QueryLogger;
use Illuminate\Support\Facades\Log;

final class FilmUseCases
{
    use LogsSearchQuery;

    public function __construct(
        private FilmRepositoryInterface $filmRepository,
        private PersonRepositoryInterface $personRepository,
        private QueryLogger $logger,
    ) {}

    protected function queryLogger(): QueryLogger
    {
        return $this->logger;
    }

    /**
     * Search films by title and return slim result listings.
     */
    public function searchByTitle(SearchQuery $query): array
    {
        return $this->executeAndLog('films', $query, function () use ($query) {
            $films = $this->filmRepository->searchByTitle($query);

            return array_map(fn ($f) => $f->toSearchResult(), $films);
        });
    }

    /**
     * Fetch full details for a single film, resolving linked character names.
     */
    public function getDetails(string $filmId): array
    {
        $film = $this->filmRepository->findById($filmId);
        $details = $film->toDetails();

        // Resolve character IDs to names so the FE can render clickable links
        $characters = [];
        foreach ($film->characterIds() as $characterId) {
            try {
                $person = $this->personRepository->findById($characterId);
                $characters[] = ['uid' => $characterId, 'name' => $person->name()];
            } catch (\Throwable $e) {
                Log::warning('Failed to resolve character for film details', [
                    'film_id' => $filmId,
                    'character_id' => $characterId,
                    'error' => $e->getMessage(),
                ]);
                $characters[] = ['uid' => $characterId, 'name' => "Character #{$characterId}"];
            }
        }

        $details['characters'] = $characters;
        unset($details['character_ids']);

        return $details;
    }
}
