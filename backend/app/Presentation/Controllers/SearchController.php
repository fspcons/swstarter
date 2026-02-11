<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\FilmUseCases;
use App\Application\UseCases\PeopleUseCases;
use App\Domain\ValueObjects\SearchQuery;
use App\Domain\ValueObjects\SearchType;
use App\Presentation\Requests\SearchRequest;
use Illuminate\Http\JsonResponse;

class SearchController
{
    public function __construct(
        private PeopleUseCases $people,
        private FilmUseCases $films,
    ) {}

    public function search(SearchRequest $request): JsonResponse
    {
        $type = SearchType::from($request->validated('type'));
        $query = new SearchQuery($request->validated('query'));
        $page = (int) $request->validated('page', 1);
        $perPage = (int) $request->validated('per_page', 10);

        $results = match ($type) {
            SearchType::People => $this->people->searchByName($query),
            SearchType::Films => $this->films->searchByTitle($query),
        };

        // Client-side pagination over the SWAPI results
        $total = count($results);
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;
        $paginated = array_values(
            array_slice($results, ($page - 1) * $perPage, $perPage)
        );

        return response()->json([
            'data' => $paginated,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
        ]);
    }
}
