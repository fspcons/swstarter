<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Application\UseCases\ComputeMetrics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecomputeMetricsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function handle(ComputeMetrics $computeMetrics): void
    {
        Log::info('Recomputing metrics snapshot');
        $computeMetrics->execute();
        Log::info('Metrics snapshot recomputed successfully');
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to recompute metrics', [
            'error' => $exception->getMessage(),
        ]);
    }
}
