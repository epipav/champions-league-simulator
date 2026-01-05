<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\Team;

class MatchSimulationService
{
    /**
     * Simulate a single match and update it with scores.
     *
     * Algorithm:
     * 1. Get team power ratings
     * 2. Apply home advantage (15% boost)
     * 3. Add randomness (±30% variance)
     * 4. Calculate expected goals using Poisson distribution
     * 5. Generate actual scores
     * 6. Mark match as played
     */
    public function simulateMatch(FootballMatch $match): FootballMatch
    {
        $homeTeam = Team::find($match->home_team_id);
        $awayTeam = Team::find($match->away_team_id);

        // Calculate adjusted power with home advantage and randomness
        $homePower = $this->calculateAdjustedPower($homeTeam->team_power, true);
        $awayPower = $this->calculateAdjustedPower($awayTeam->team_power, false);

        // Calculate expected goals based on adjusted power
        $homeExpectedGoals = $this->calculateExpectedGoals($homePower, $awayPower);
        $awayExpectedGoals = $this->calculateExpectedGoals($awayPower, $homePower);

        // Generate actual scores using Poisson distribution
        $homeScore = $this->generateScore($homeExpectedGoals);
        $awayScore = $this->generateScore($awayExpectedGoals);

        // Update match
        $match->home_score = $homeScore;
        $match->away_score = $awayScore;
        $match->is_played = true;
        $match->save();

        return $match;
    }

    /**
     * Calculate adjusted power with home advantage and randomness.
     */
    private function calculateAdjustedPower(int $basePower, bool $isHome): float
    {
        $power = (float) $basePower;

        // Apply 15% home advantage
        if ($isHome) {
            $power *= 1.15;
        }

        // Add ±30% randomness
        $randomFactor = 1.0 + (mt_rand(-30, 30) / 100);
        $power *= $randomFactor;

        return max(1, $power); // Ensure positive power
    }

    /**
     * Calculate expected goals based on power difference.
     *
     * Formula: expectedGoals = (teamPower / opponentPower) * 1.5
     * This gives ~1.5 goals for evenly matched teams
     */
    private function calculateExpectedGoals(float $teamPower, float $opponentPower): float
    {
        $ratio = $teamPower / $opponentPower;
        $expectedGoals = $ratio * 1.5;

        // Cap expected goals between 0.2 and 5.0
        return max(0.2, min(5.0, $expectedGoals));
    }

    /**
     * Generate actual score using Poisson distribution.
     *
     * Poisson distribution is commonly used in football score prediction.
     * It models the probability of a number of events occurring in a fixed interval.
     */
    private function generateScore(float $expectedGoals): int
    {
        // Use inverse transform sampling for Poisson distribution
        $L = exp(-$expectedGoals);
        $k = 0;
        $p = 1.0;

        do {
            $k++;
            $u = mt_rand() / mt_getrandmax();
            $p *= $u;
        } while ($p > $L && $k < 15); // Cap at 15 goals for safety

        return max(0, $k - 1);
    }
}
