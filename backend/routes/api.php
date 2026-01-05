<?php

use App\Http\Controllers\Api\LeagueController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Champions League API v1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // League endpoints
    Route::get('/league/standings', [LeagueController::class, 'standings']);
    Route::get('/league/state', [LeagueController::class, 'state']);
    Route::post('/league/initialize', [MatchController::class, 'initialize']);
    Route::post('/league/full-reset', [MatchController::class, 'fullReset']);

    // Team endpoints
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('/teams/{id}', [TeamController::class, 'show']);

    // Match endpoints
    Route::get('/matches', [MatchController::class, 'index']);
    Route::get('/matches/week/{week}', [MatchController::class, 'byWeek']);
    Route::put('/matches/{id}', [MatchController::class, 'update']);
    Route::post('/matches/play-week', [MatchController::class, 'playWeek']);
    Route::post('/matches/play-all', [MatchController::class, 'playAll']);
    Route::post('/matches/reset', [MatchController::class, 'reset']);

    // Prediction endpoints
    Route::get('/predictions', [PredictionController::class, 'index']);
});
