<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\Person;
use App\Domain\ValueObjects\SearchQuery;

interface PersonRepositoryInterface
{
    /**
     * @return Person[]
     */
    public function searchByName(SearchQuery $query): array;

    public function findById(string $id): Person;
}
