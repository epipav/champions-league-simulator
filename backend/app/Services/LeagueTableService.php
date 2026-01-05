<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\Team;
use Illuminate\Support\Collection;

class LeagueTableService
{
    /**
     * Get the current league standings.
     *
     * Returns an array of team standings with position, stats, and points.
     * Sorted by Premier League rules: Points > Goal Difference > Goals For > Team Name
     */
    public function getStandings(): array
    {
        $teams = Team::all();
        $playedMatches = FootballMatch::where('is_played', true)->get();

        $standings = [];

        foreach ($teams as $team) {
            $standings[] = $this->calculateTeamStats($team, $playedMatches);
        }

        // Sort by Premier League rules
        usort($standings, function ($a, $b) {
            // 1. Points (descending)
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }

            // 2. Goal Difference (descending)
            if ($a['goal_difference'] !== $b['goal_difference']) {
                return $b['goal_difference'] <=> $a['goal_difference'];
            }

            // 3. Goals For (descending)
            if ($a['goals_for'] !== $b['goals_for']) {
                return $b['goals_for'] <=> $a['goals_for'];
            }

            // 4. Team Name (alphabetical)
            return $a['team']['name'] <=> $b['team']['name'];
        });

        // Add position numbers
        foreach ($standings as $index => &$standing) {
            $standing['position'] = $index + 1;
        }

        return $standings;
    }

    /**
     * Calculate statistics for a single team.
     */
    private function calculateTeamStats(Team $team, Collection $playedMatches): array
    {
        $teamMatches = $playedMatches->filter(function ($match) use ($team) {
            return $match->home_team_id === $team->id || $match->away_team_id === $team->id;
        });

        $stats = [
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
        ];

        foreach ($teamMatches as $match) {
            $stats['played']++;

            $isHome = $match->home_team_id === $team->id;
            $goalsFor = $isHome ? $match->home_score : $match->away_score;
            $goalsAgainst = $isHome ? $match->away_score : $match->home_score;

            $stats['goals_for'] += $goalsFor;
            $stats['goals_against'] += $goalsAgainst;

            if ($goalsFor > $goalsAgainst) {
                $stats['won']++;
            } elseif ($goalsFor === $goalsAgainst) {
                $stats['drawn']++;
            } else {
                $stats['lost']++;
            }
        }

        // Calculate derived stats
        $stats['goal_difference'] = $stats['goals_for'] - $stats['goals_against'];
        $stats['points'] = ($stats['won'] * 3) + ($stats['drawn'] * 1);

        // Add team info
        $stats['team'] = [
            'id' => $team->id,
            'name' => $team->name,
            'logo_url' => $team->logo_url,
            'team_power' => $team->team_power,
        ];

        // Position will be added after sorting
        $stats['position'] = 0;

        return $stats;
    }

    /**
     * Get standings for a specific team.
     */
    public function getTeamStanding(int $teamId): ?array
    {
        $standings = $this->getStandings();

        foreach ($standings as $standing) {
            if ($standing['team']['id'] === $teamId) {
                return $standing;
            }
        }

        return null;
    }
}
