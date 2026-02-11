<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use App\Infrastructure\ExternalServices\SwapiClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryLogger
{
    public function __construct(
        private SwapiClient $swapiClient,
    ) {}

    /**
     * Log a search query for metrics aggregation.
     */
    public function log(
        string $type,
        string $query,
        float $durationMs,
        int $resultCount,
        bool $cached,
        bool $isError = false,
    ): void {
        try {
            DB::table('query_logs')->insert([
                'search_type' => $type,
                'search_query' => $query,
                'duration_ms' => round($durationMs, 2),
                'result_count' => $resultCount,
                'cached' => $cached,
                'is_error' => $isError,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to write query log', [
                'error' => $e->getMessage(),
                'type' => $type,
                'query' => $query,
            ]);
        }
    }

    /**
     * Delegate to SwapiClient to check if the last request was served from cache.
     */
    public function wasLastRequestCached(): bool
    {
        return $this->swapiClient->wasLastRequestCached();
    }
}
