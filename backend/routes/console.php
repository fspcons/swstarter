<?php

use App\Jobs\RecomputeMetricsJob;
use Illuminate\Support\Facades\Schedule;

/*
 * Metrics are recomputed every 5 minutes via the queue system,
 * as required. The scheduler dispatches the job to Redis queue,
 * and the queue worker picks it up for processing.
 */
Schedule::job(new RecomputeMetricsJob)->everyFiveMinutes();
