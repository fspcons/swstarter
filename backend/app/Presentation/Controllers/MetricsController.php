<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\ComputeMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class MetricsController
{
    public function __construct(
        private ComputeMetrics $computeMetrics,
    ) {}

    public function index(): JsonResponse
    {
        $metrics = Redis::get('metrics:latest');

        // First request or cache expired — compute on the spot
        if ($metrics === null) {
            $this->computeMetrics->execute();
            $metrics = Redis::get('metrics:latest');
        }

        return response()->json([
            'data' => json_decode((string) $metrics, true),
        ]);
    }
}
