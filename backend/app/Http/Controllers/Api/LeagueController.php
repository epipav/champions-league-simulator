<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeagueState;
use App\Services\LeagueTableService;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    private LeagueTableService $leagueTableService;

    public function __construct(LeagueTableService $leagueTableService)
    {
        $this->leagueTableService = $leagueTableService;
    }

    /**
     * Get current league standings.
     */
    public function standings(): JsonResponse
    {
        $standings = $this->leagueTableService->getStandings();

        return response()->json($standings);
    }

    /**
     * Get current league state (current week).
     */
    public function state(): JsonResponse
    {
        $state = LeagueState::first();

        if (! $state) {
            return response()->json([
                'message' => 'League not initialized',
            ], 404);
        }

        return response()->json([
            'current_week' => $state->current_week,
            'is_completed' => $state->current_week >= 6,
        ]);
    }
}
