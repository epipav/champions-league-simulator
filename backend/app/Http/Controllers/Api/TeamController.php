<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\LeagueTableService;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    private LeagueTableService $leagueTableService;

    public function __construct(LeagueTableService $leagueTableService)
    {
        $this->leagueTableService = $leagueTableService;
    }

    /**
     * Get all teams.
     */
    public function index(): JsonResponse
    {
        $teams = Team::all();

        return response()->json($teams);
    }

    /**
     * Get a specific team with its standing.
     */
    public function show(int $id): JsonResponse
    {
        $team = Team::find($id);

        if (! $team) {
            return response()->json([
                'message' => 'Team not found',
            ], 404);
        }

        $standing = $this->leagueTableService->getTeamStanding($id);

        return response()->json([
            'team' => $team,
            'standing' => $standing,
        ]);
    }
}
