<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\FilmUseCases;
use Illuminate\Http\JsonResponse;

class FilmsController
{
    public function __construct(
        private FilmUseCases $films,
    ) {}

    public function show(string $id): JsonResponse
    {
        $details = $this->films->getDetails($id);

        return response()->json($details);
    }
}
