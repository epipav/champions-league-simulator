<?php

namespace App\Services;

class FixtureGeneratorService
{
    /**
     * Generate round-robin fixtures for 4 teams across 6 weeks.
     *
     * Each team plays every other team twice (home and away).
     * Total matches: 12 (2 matches per week × 6 weeks)
     *
     * @param  array  $teamIds  Array of 4 team IDs
     * @return array Array of fixture data
     */
    public function generateFixtures(array $teamIds): array
    {
        if (count($teamIds) !== 4) {
            throw new \InvalidArgumentException('Exactly 4 team IDs are required');
        }

        // Shuffle team order to randomize fixtures each time
        shuffle($teamIds);

        // Round-robin algorithm for 4 teams
        // First round (weeks 1-3): each team plays each other once
        // Second round (weeks 4-6): reverse home/away

        $fixtures = [
            // Week 1
            1 => [
                [$teamIds[0], $teamIds[1]], // T1 vs T2
                [$teamIds[2], $teamIds[3]], // T3 vs T4
            ],
            // Week 2
            2 => [
                [$teamIds[0], $teamIds[2]], // T1 vs T3
                [$teamIds[1], $teamIds[3]], // T2 vs T4
            ],
            // Week 3
            3 => [
                [$teamIds[0], $teamIds[3]], // T1 vs T4
                [$teamIds[1], $teamIds[2]], // T2 vs T3
            ],
            // Week 4 (reversed from week 1)
            4 => [
                [$teamIds[1], $teamIds[0]], // T2 vs T1
                [$teamIds[3], $teamIds[2]], // T4 vs T3
            ],
            // Week 5 (reversed from week 2)
            5 => [
                [$teamIds[2], $teamIds[0]], // T3 vs T1
                [$teamIds[3], $teamIds[1]], // T4 vs T2
            ],
            // Week 6 (reversed from week 3)
            6 => [
                [$teamIds[3], $teamIds[0]], // T4 vs T1
                [$teamIds[2], $teamIds[1]], // T3 vs T2
            ],
        ];

        $matches = [];

        foreach ($fixtures as $week => $weekMatches) {
            foreach ($weekMatches as $match) {
                $matches[] = [
                    'home_team_id' => $match[0],
                    'away_team_id' => $match[1],
                    'week' => $week,
                    'is_played' => false,
                    'home_score' => null,
                    'away_score' => null,
                ];
            }
        }

        return $matches;
    }
}
