<?php

use App\Presentation\Controllers\FilmsController;
use App\Presentation\Controllers\MetricsController;
use App\Presentation\Controllers\PeopleController;
use App\Presentation\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/search', [SearchController::class, 'search']);
Route::get('/people/{id}', [PeopleController::class, 'show']);
Route::get('/films/{id}', [FilmsController::class, 'show']);
Route::get('/metrics', [MetricsController::class, 'index']);
