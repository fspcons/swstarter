<?php

declare(strict_types=1);

namespace App\Application\Concerns;

use App\Domain\ValueObjects\SearchQuery;
use App\Infrastructure\Logging\QueryLogger;

/**
 * Reusable timing / logging wrapper shared by all search use cases.
 *
 * The consuming class must expose a QueryLogger via the abstract getter.
 */
trait LogsSearchQuery
{
    abstract protected function queryLogger(): QueryLogger;

    /**
     * Execute a search callback while measuring elapsed time and logging
     * the query metadata (type, duration, cached status, errors).
     *
     * @param  string  $type  The search type label (e.g. 'people', 'films').
     * @param  SearchQuery  $query  The user's search query.
     * @param  callable(): array  $searchFn  The actual search logic.
     * @return array The search results.
     */
    protected function executeAndLog(string $type, SearchQuery $query, callable $searchFn): array
    {
        $startTime = microtime(true);
        $isError = false;
        $results = [];

        try {
            $results = $searchFn();

            return $results;
        } catch (\Throwable $e) {
            $isError = true;
            throw $e;
        } finally {
            $durationMs = (microtime(true) - $startTime) * 1000;

            $this->queryLogger()->log(
                type: $type,
                query: $query->value,
                durationMs: $durationMs,
                resultCount: count($results),
                cached: $this->queryLogger()->wasLastRequestCached(),
                isError: $isError,
            );
        }
    }
}
