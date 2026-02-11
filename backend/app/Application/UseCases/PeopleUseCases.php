<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Concerns\LogsSearchQuery;
use App\Domain\Repositories\FilmRepositoryInterface;
use App\Domain\Repositories\PersonRepositoryInterface;
use App\Domain\ValueObjects\SearchQuery;
use App\Infrastructure\Logging\QueryLogger;
use Illuminate\Support\Facades\Log;

final class PeopleUseCases
{
    use LogsSearchQuery;

    public function __construct(
        private PersonRepositoryInterface $personRepository,
        private FilmRepositoryInterface $filmRepository,
        private QueryLogger $logger,
    ) {}

    protected function queryLogger(): QueryLogger
    {
        return $this->logger;
    }

    /**
     * Search people by name and return slim result listings.
     */
    public function searchByName(SearchQuery $query): array
    {
        return $this->executeAndLog('people', $query, function () use ($query) {
            $people = $this->personRepository->searchByName($query);

            return array_map(fn ($p) => $p->toSearchResult(), $people);
        });
    }

    /**
     * Fetch full details for a single person, resolving linked film titles.
     */
    public function getDetails(string $personId): array
    {
        $person = $this->personRepository->findById($personId);
        $details = $person->toDetails();

        // Resolve film IDs to titles so the FE can render clickable links
        $films = [];
        foreach ($person->filmIds() as $filmId) {
            try {
                $film = $this->filmRepository->findById($filmId);
                $films[] = ['uid' => $filmId, 'title' => $film->title()];
            } catch (\Throwable $e) {
                Log::warning('Failed to resolve film for person details', [
                    'person_id' => $personId,
                    'film_id' => $filmId,
                    'error' => $e->getMessage(),
                ]);
                $films[] = ['uid' => $filmId, 'title' => "Film #{$filmId}"];
            }
        }

        $details['films'] = $films;
        unset($details['film_ids']);

        return $details;
    }
}
