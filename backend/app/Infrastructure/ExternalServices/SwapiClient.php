<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalServices;

use App\Domain\Exceptions\ExternalServiceException;
use App\Domain\Exceptions\RateLimitExceededException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SwapiClient
{
    private const BASE_URL = 'https://www.swapi.tech/api';

    private const CACHE_TTL_SECONDS = 600; // 10 minutes

    private const REQUEST_TIMEOUT = 10;

    private bool $lastRequestWasCached = false;

    public function searchPeople(string $name): array
    {
        return $this->cachedGet('/people/?name='.urlencode($name));
    }

    public function searchFilms(string $title): array
    {
        return $this->cachedGet('/films/?title='.urlencode($title));
    }

    public function getPerson(string $id): array
    {
        return $this->cachedGet("/people/{$id}");
    }

    public function getFilm(string $id): array
    {
        return $this->cachedGet("/films/{$id}");
    }

    public function wasLastRequestCached(): bool
    {
        return $this->lastRequestWasCached;
    }

    private function cachedGet(string $endpoint): array
    {
        $cacheKey = 'swapi:'.md5($endpoint);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            $this->lastRequestWasCached = true;
            Log::debug('SWAPI cache hit', ['endpoint' => $endpoint]);

            return $cached;
        }

        $this->lastRequestWasCached = false;
        $url = self::BASE_URL.$endpoint;

        Log::info('SWAPI request', ['url' => $url]);

        try {
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->retry(2, 500, throw: false)
                ->get($url);
        } catch (\Throwable $e) {
            Log::error('SWAPI request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            throw ExternalServiceException::swapiUnavailable($e->getMessage());
        }

        if ($response->status() === 429) {
            Log::warning('SWAPI rate limit exceeded', ['endpoint' => $endpoint]);
            throw new RateLimitExceededException;
        }

        if ($response->status() === 404) {
            Log::info('SWAPI resource not found', ['endpoint' => $endpoint]);

            return [];
        }

        if ($response->failed()) {
            Log::error('SWAPI returned error status', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => mb_substr($response->body(), 0, 500),
            ]);
            throw ExternalServiceException::swapiUnavailable(
                "HTTP {$response->status()}"
            );
        }

        $data = $response->json();

        Cache::put($cacheKey, $data, self::CACHE_TTL_SECONDS);

        return $data;
    }
}
