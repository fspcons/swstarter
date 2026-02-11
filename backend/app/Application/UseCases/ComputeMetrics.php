<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

final class ComputeMetrics
{
    /**
     * Recompute all metrics from the query_logs table and store
     * the snapshot in Redis for fast retrieval by the metrics endpoint.
     */
    public function execute(): void
    {
        $totalQueries = DB::table('query_logs')->count();

        // a. Top 5 queries with percentages
        $topQueries = DB::table('query_logs')
            ->select('search_type', 'search_query', DB::raw('COUNT(*) as count'))
            ->groupBy('search_type', 'search_query')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'query' => "{$row->search_type}:{$row->search_query}",
                'count' => (int) $row->count,
                'percentage' => $totalQueries > 0
                    ? round(($row->count / $totalQueries) * 100, 2)
                    : 0,
            ])
            ->toArray();

        // b. Average request duration
        $avgDuration = DB::table('query_logs')->avg('duration_ms') ?? 0;

        // c. Cached vs Non-Cached
        $cachedCount = DB::table('query_logs')->where('cached', true)->count();
        $nonCachedCount = $totalQueries - $cachedCount;

        // d. Error vs Success percentages
        $errorCount = DB::table('query_logs')->where('is_error', true)->count();
        $successCount = $totalQueries - $errorCount;

        $metrics = [
            'top_queries' => $topQueries,
            'average_duration_ms' => round((float) $avgDuration, 2),
            'cache_stats' => [
                'cached' => $cachedCount,
                'non_cached' => $nonCachedCount,
                'cached_percentage' => $totalQueries > 0
                    ? round(($cachedCount / $totalQueries) * 100, 2)
                    : 0,
                'non_cached_percentage' => $totalQueries > 0
                    ? round(($nonCachedCount / $totalQueries) * 100, 2)
                    : 0,
            ],
            'status_stats' => [
                'success' => $successCount,
                'error' => $errorCount,
                'success_percentage' => $totalQueries > 0
                    ? round(($successCount / $totalQueries) * 100, 2)
                    : 0,
                'error_percentage' => $totalQueries > 0
                    ? round(($errorCount / $totalQueries) * 100, 2)
                    : 0,
            ],
            'total_queries' => $totalQueries,
            'computed_at' => now()->toISOString(),
        ];

        Redis::set('metrics:latest', json_encode($metrics));
    }
}
