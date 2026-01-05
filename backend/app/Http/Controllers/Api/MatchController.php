<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\LeagueState;
use App\Models\Team;
use App\Services\MatchSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MatchController extends Controller
{
    private MatchSimulationService $matchSimulationService;

    public function __construct(MatchSimulationService $matchSimulationService)
    {
        $this->matchSimulationService = $matchSimulationService;
    }

    /**
     * Get all matches.
     */
    public function index(): JsonResponse
    {
        $matches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->get();

        return response()->json($matches);
    }

    /**
     * Get matches for a specific week.
     */
    public function byWeek(int $week): JsonResponse
    {
        if ($week < 1 || $week > 6) {
            return response()->json([
                'message' => 'Week must be between 1 and 6',
            ], 400);
        }

        $matches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('week', $week)
            ->get();

        return response()->json($matches);
    }

    /**
     * Play all matches for the current week.
     */
    public function playWeek(): JsonResponse
    {
        $leagueState = LeagueState::first();

        if (! $leagueState) {
            return response()->json([
                'message' => 'League not initialized',
            ], 404);
        }

        $nextWeek = $leagueState->current_week + 1;

        if ($nextWeek > 6) {
            return response()->json([
                'message' => 'All weeks have been played',
            ], 400);
        }

        // Get matches for the next week
        $matches = FootballMatch::where('week', $nextWeek)
            ->where('is_played', false)
            ->get();

        if ($matches->isEmpty()) {
            return response()->json([
                'message' => "No unplayed matches found for week {$nextWeek}",
            ], 404);
        }

        // Simulate all matches
        $results = [];
        foreach ($matches as $match) {
            $this->matchSimulationService->simulateMatch($match);
            $match->load(['homeTeam', 'awayTeam']);
            $results[] = $match;
        }

        // Update league state
        $leagueState->current_week = $nextWeek;
        $leagueState->save();

        return response()->json([
            'week' => $nextWeek,
            'matches' => $results,
            'message' => "Week {$nextWeek} matches played successfully",
        ]);
    }

    /**
     * Play all remaining weeks at once.
     */
    public function playAll(): JsonResponse
    {
        $leagueState = LeagueState::first();

        if (! $leagueState) {
            return response()->json([
                'message' => 'League not initialized',
            ], 404);
        }

        if ($leagueState->current_week >= 6) {
            return response()->json([
                'message' => 'All weeks have already been played',
            ], 400);
        }

        $weeksPlayed = [];

        // Play remaining weeks
        for ($week = $leagueState->current_week + 1; $week <= 6; $week++) {
            $matches = FootballMatch::where('week', $week)
                ->where('is_played', false)
                ->get();

            $weekResults = [];
            foreach ($matches as $match) {
                $this->matchSimulationService->simulateMatch($match);
                $match->load(['homeTeam', 'awayTeam']);
                $weekResults[] = $match;
            }

            $weeksPlayed[] = [
                'week' => $week,
                'matches' => $weekResults,
            ];
        }

        // Update league state to week 6
        $leagueState->current_week = 6;
        $leagueState->save();

        return response()->json($weeksPlayed);
    }

    /**
     * Update a match score manually.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $match = FootballMatch::find($id);

        if (! $match) {
            return response()->json([
                'message' => 'Match not found',
            ], 404);
        }

        $validated = $request->validate([
            'home_score' => 'required|integer|min:0|max:99',
            'away_score' => 'required|integer|min:0|max:99',
        ]);

        $match->home_score = $validated['home_score'];
        $match->away_score = $validated['away_score'];
        $match->is_played = true;
        $match->save();

        $match->load(['homeTeam', 'awayTeam']);

        return response()->json($match);
    }

    /**
     * Reset the league (for testing).
     */
    public function reset(): JsonResponse
    {
        // Reset all matches
        FootballMatch::query()->update([
            'is_played' => false,
            'home_score' => null,
            'away_score' => null,
        ]);

        // Reset league state
        $leagueState = LeagueState::first();
        if ($leagueState) {
            $leagueState->current_week = 0;
            $leagueState->save();
        }

        return response()->json([
            'message' => 'League reset successfully',
        ]);
    }

    /**
     * Initialize the league with teams and fixtures.
     */
    public function initialize(): JsonResponse
    {
        // Check if already initialized
        if (Team::count() > 0) {
            return response()->json([
                'message' => 'League already initialized. Use reset to start over.',
            ], 400);
        }

        // Run the database seeders
        Artisan::call('db:seed', ['--force' => true]);

        return response()->json([
            'teams' => Team::count(),
            'fixtures' => FootballMatch::count(),
            'message' => 'League initialized successfully',
        ]);
    }

    /**
     * Full reset - delete everything and reinitialize.
     */
    public function fullReset(): JsonResponse
    {
        // Delete all data
        FootballMatch::query()->delete();
        LeagueState::query()->delete();
        Team::query()->delete();

        // Run the database seeders
        Artisan::call('db:seed', ['--force' => true]);

        return response()->json([
            'teams' => Team::count(),
            'fixtures' => FootballMatch::count(),
            'message' => 'League fully reset and reinitialized',
        ]);
    }
}
