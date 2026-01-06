<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\LeagueState;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    private const SIMULATIONS = 100;

    private const HOME_ADVANTAGE = 1.15;
    // private const RANDOMNESS = 0.30;

    private MatchSimulationService $matchSimulator;

    public function __construct()
    {
        $this->matchSimulator = new MatchSimulationService;
    }

    /**
     * Get championship predictions using Monte Carlo simulation.
     * Only available from week 4 onwards.
     *
     * @return array|null Returns null if week < 4
     */
    public function getPredictions(): ?array
    {
        Log::info('Message here');

        $leagueState = LeagueState::first();

        if (! $leagueState || $leagueState->current_week < 4) {
            return null;
        }

        $teams = Team::all();
        $championshipWins = [];

        foreach ($teams as $team) {
            $championshipWins[$team->id] = 0;
        }

        // Run Monte Carlo simulations
        for ($i = 0; $i < self::SIMULATIONS; $i++) {
            $winnerId = $this->simulateRemainingSeasonAndGetWinner($leagueState->current_week);
            $championshipWins[$winnerId]++;
        }

        // Convert to percentages
        $predictions = [];
        foreach ($teams as $team) {
            $predictions[] = [
                'team' => [
                    'id' => $team->id,
                    'name' => $team->name,
                    'logo_url' => $team->logo_url,
                ],
                'probability' => round(($championshipWins[$team->id] / self::SIMULATIONS) * 100, 1),
            ];
        }

        // Sort by probability descending
        usort($predictions, fn ($a, $b) => $b['probability'] <=> $a['probability']);

        return $predictions;
    }

    /**
     * Simulate remaining matches and determine the winner.
     *
     * @return int Winner team ID
     */
    private function simulateRemainingSeasonAndGetWinner(int $currentWeek): int
    {
        // Get current standings from played matches
        $standings = $this->calculateCurrentStandings();

        // Simulate remaining matches
        $remainingMatches = FootballMatch::where('is_played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        foreach ($remainingMatches as $match) {
            $result = $this->simulateMatchResult(
                $match->homeTeam->team_power,
                $match->awayTeam->team_power
            );

            // Update standings
            $this->updateStandings(
                $standings,
                $match->home_team_id,
                $match->away_team_id,
                $result['home_score'],
                $result['away_score']
            );
        }

        // Return team with highest points (apply tiebreakers)
        return $this->getWinner($standings);
    }

    /**
     * Calculate current standings from played matches.
     */
    private function calculateCurrentStandings(): array
    {
        $teams = Team::all();
        $standings = [];

        foreach ($teams as $team) {
            $standings[$team->id] = [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'points' => 0,
                'goal_difference' => 0,
                'goals_for' => 0,
            ];
        }

        $playedMatches = FootballMatch::where('is_played', true)->get();

        foreach ($playedMatches as $match) {
            $homeId = $match->home_team_id;
            $awayId = $match->away_team_id;
            $homeScore = $match->home_score;
            $awayScore = $match->away_score;

            // Goals
            $standings[$homeId]['goals_for'] += $homeScore;
            $standings[$awayId]['goals_for'] += $awayScore;
            $standings[$homeId]['goal_difference'] += ($homeScore - $awayScore);
            $standings[$awayId]['goal_difference'] += ($awayScore - $homeScore);

            // Points
            if ($homeScore > $awayScore) {
                $standings[$homeId]['points'] += 3;
            } elseif ($awayScore > $homeScore) {
                $standings[$awayId]['points'] += 3;
            } else {
                $standings[$homeId]['points'] += 1;
                $standings[$awayId]['points'] += 1;
            }
        }

        return $standings;
    }

    /**
     * Simulate a match result without saving to database.
     *
     * @return array ['home_score', 'away_score']
     */
    private function simulateMatchResult(int $homePower, int $awayPower): array
    {
        // Apply home advantage and randomness
        $adjustedHomePower = $homePower * self::HOME_ADVANTAGE * (1 + (mt_rand(-30, 30) / 100));
        $adjustedAwayPower = $awayPower * (1 + (mt_rand(-30, 30) / 100));

        // Calculate expected goals
        $homeExpected = max(0.2, min(5.0, ($adjustedHomePower / $adjustedAwayPower) * 1.5));
        $awayExpected = max(0.2, min(5.0, ($adjustedAwayPower / $adjustedHomePower) * 1.5));

        return [
            'home_score' => $this->poissonRandom($homeExpected),
            'away_score' => $this->poissonRandom($awayExpected),
        ];
    }

    /**
     * Generate Poisson-distributed random number.
     *
     * @param  float  $lambda  Expected value
     */
    private function poissonRandom(float $lambda): int
    {
        $L = exp(-$lambda);
        $k = 0;
        $p = 1.0;

        do {
            $k++;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $L && $k < 15);

        return max(0, $k - 1);
    }

    /**
     * Update standings with match result.
     */
    private function updateStandings(array &$standings, int $homeId, int $awayId, int $homeScore, int $awayScore): void
    {
        $standings[$homeId]['goals_for'] += $homeScore;
        $standings[$awayId]['goals_for'] += $awayScore;
        $standings[$homeId]['goal_difference'] += ($homeScore - $awayScore);
        $standings[$awayId]['goal_difference'] += ($awayScore - $homeScore);

        if ($homeScore > $awayScore) {
            $standings[$homeId]['points'] += 3;
        } elseif ($awayScore > $homeScore) {
            $standings[$awayId]['points'] += 3;
        } else {
            $standings[$homeId]['points'] += 1;
            $standings[$awayId]['points'] += 1;
        }
    }

    /**
     * Determine the winner from standings using Premier League tiebreakers.
     *
     * @return int Winner team ID
     */
    private function getWinner(array $standings): int
    {
        // Sort by: points desc, goal_difference desc, goals_for desc, team_name asc
        uasort($standings, function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] - $a['points'];
            }
            if ($a['goal_difference'] !== $b['goal_difference']) {
                return $b['goal_difference'] - $a['goal_difference'];
            }
            if ($a['goals_for'] !== $b['goals_for']) {
                return $b['goals_for'] - $a['goals_for'];
            }

            return strcmp($a['team_name'], $b['team_name']);
        });

        // Return first team's ID (the winner)
        return array_key_first($standings);
    }
}
