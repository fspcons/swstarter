<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Repositories\FilmRepositoryInterface;
use App\Domain\Repositories\PersonRepositoryInterface;
use App\Infrastructure\ExternalServices\SwapiClient;
use App\Infrastructure\Logging\QueryLogger;
use App\Infrastructure\Repositories\SwapiFilmRepository;
use App\Infrastructure\Repositories\SwapiPersonRepository;
use Illuminate\Support\ServiceProvider;

/**
 * All dependency injection bindings are registered here at boot time,
 * keeping the container configuration in one place. The container resolves
 * these automatically via constructor injection during the request lifecycle.
 */
class SwapiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // SwapiClient as a singleton so cache-hit tracking persists within a request
        $this->app->singleton(SwapiClient::class);

        // Bind repository interfaces to their SWAPI implementations
        $this->app->bind(
            PersonRepositoryInterface::class,
            SwapiPersonRepository::class
        );

        $this->app->bind(
            FilmRepositoryInterface::class,
            SwapiFilmRepository::class
        );

        // QueryLogger depends on SwapiClient for cache tracking
        $this->app->singleton(QueryLogger::class);
    }
}
