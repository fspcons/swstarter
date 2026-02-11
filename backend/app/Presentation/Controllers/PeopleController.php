<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\PeopleUseCases;
use Illuminate\Http\JsonResponse;

class PeopleController
{
    public function __construct(
        private PeopleUseCases $people,
    ) {}

    public function show(string $id): JsonResponse
    {
        $details = $this->people->getDetails($id);

        return response()->json($details);
    }
}
