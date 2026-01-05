<?php

namespace Tests\Unit;

use App\Services\FixtureGeneratorService;
use PHPUnit\Framework\TestCase;

class FixtureGeneratorServiceTest extends TestCase
{
    private FixtureGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FixtureGeneratorService;
    }

    /**
     * Test that the service generates exactly 12 matches for 4 teams.
     *
     * @return void
     */
    public function test_generates_12_matches_for_4_teams()
    {
        $teamIds = [1, 2, 3, 4];

        $fixtures = $this->service->generateFixtures($teamIds);

        $this->assertCount(12, $fixtures, 'Should generate 12 matches for round-robin with 4 teams');
    }

    /**
     * Test that each team plays exactly 6 matches (3 opponents × 2 home/away).
     *
     * @return void
     */
    public function test_each_team_plays_6_matches()
    {
        $teamIds = [1, 2, 3, 4];

        $fixtures = $this->service->generateFixtures($teamIds);

        // Count matches for each team
        foreach ($teamIds as $teamId) {
            $matchCount = 0;
            foreach ($fixtures as $fixture) {
                if ($fixture['home_team_id'] === $teamId || $fixture['away_team_id'] === $teamId) {
                    $matchCount++;
                }
            }
            $this->assertEquals(6, $matchCount, "Team $teamId should play exactly 6 matches");
        }
    }

    /**
     * Test that round-robin includes all pairings (each team plays each other team twice).
     *
     * @return void
     */
    public function test_round_robin_includes_all_pairings()
    {
        $teamIds = [1, 2, 3, 4];

        $fixtures = $this->service->generateFixtures($teamIds);

        // Check all unique pairings
        $pairings = [
            [1, 2], [1, 3], [1, 4],
            [2, 3], [2, 4],
            [3, 4],
        ];

        foreach ($pairings as $pair) {
            $homeAwayCount = 0;
            $awayHomeCount = 0;

            foreach ($fixtures as $fixture) {
                if ($fixture['home_team_id'] === $pair[0] && $fixture['away_team_id'] === $pair[1]) {
                    $homeAwayCount++;
                }
                if ($fixture['home_team_id'] === $pair[1] && $fixture['away_team_id'] === $pair[0]) {
                    $awayHomeCount++;
                }
            }

            $this->assertEquals(1, $homeAwayCount, "Team {$pair[0]} should play Team {$pair[1]} at home once");
            $this->assertEquals(1, $awayHomeCount, "Team {$pair[1]} should play Team {$pair[0]} at home once");
        }
    }

    /**
     * Test that fixtures are distributed across 6 weeks with 2 matches per week.
     *
     * @return void
     */
    public function test_fixtures_distributed_across_6_weeks()
    {
        $teamIds = [1, 2, 3, 4];

        $fixtures = $this->service->generateFixtures($teamIds);

        // Group by week
        $weekCounts = [];
        foreach ($fixtures as $fixture) {
            $week = $fixture['week'];
            $weekCounts[$week] = ($weekCounts[$week] ?? 0) + 1;
        }

        // Should have 6 weeks
        $this->assertCount(6, $weekCounts, 'Should have matches across 6 weeks');

        // Each week should have 2 matches
        for ($week = 1; $week <= 6; $week++) {
            $this->assertEquals(2, $weekCounts[$week], "Week $week should have exactly 2 matches");
        }
    }

    /**
     * Test that all matches are initially unplayed.
     *
     * @return void
     */
    public function test_matches_are_initially_unplayed()
    {
        $teamIds = [1, 2, 3, 4];

        $fixtures = $this->service->generateFixtures($teamIds);

        foreach ($fixtures as $fixture) {
            $this->assertFalse($fixture['is_played'], 'All matches should be initially unplayed');
            $this->assertNull($fixture['home_score'], 'Home score should be null for unplayed matches');
            $this->assertNull($fixture['away_score'], 'Away score should be null for unplayed matches');
        }
    }

    /**
     * Test that a team doesn't play against itself.
     *
     * @return void
     */
    public function test_team_does_not_play_itself()
    {
        $teamIds = [1, 2, 3, 4];

        $fixtures = $this->service->generateFixtures($teamIds);

        foreach ($fixtures as $fixture) {
            $this->assertNotEquals(
                $fixture['home_team_id'],
                $fixture['away_team_id'],
                'A team should not play against itself'
            );
        }
    }
}
