<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Film;
use App\Domain\ValueObjects\SearchQuery;

interface FilmRepositoryInterface
{
    /**
     * @return Film[]
     */
    public function searchByTitle(SearchQuery $query): array;

    public function findById(string $id): Film;
}
