<?php

namespace Tests\Unit;

use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\LeagueTableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeagueTableServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeagueTableService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeagueTableService;
    }

    /**
     * Test that points are calculated correctly (Win=3, Draw=1, Loss=0).
     */
    public function test_calculates_points_correctly()
    {
        // Create teams
        $team1 = Team::factory()->create(['name' => 'Team A']);
        $team2 = Team::factory()->create(['name' => 'Team B']);
        $team3 = Team::factory()->create(['name' => 'Team C']);

        // Team A: 2 wins, 1 draw = 7 points
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 3,
            'away_score' => 1,
            'is_played' => true,
        ]);
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team3->id,
            'home_score' => 2,
            'away_score' => 0,
            'is_played' => true,
        ]);
        FootballMatch::factory()->create([
            'home_team_id' => $team2->id,
            'away_team_id' => $team1->id,
            'home_score' => 1,
            'away_score' => 1,
            'is_played' => true,
        ]);

        $standings = $this->service->getStandings();

        $team1Standing = collect($standings)->firstWhere('team.id', $team1->id);
        $this->assertEquals(7, $team1Standing['points'], 'Team A should have 7 points (2 wins + 1 draw)');
        $this->assertEquals(2, $team1Standing['won']);
        $this->assertEquals(1, $team1Standing['drawn']);
        $this->assertEquals(0, $team1Standing['lost']);
    }

    /**
     * Test that goal difference is calculated correctly.
     */
    public function test_calculates_goal_difference_correctly()
    {
        $team1 = Team::factory()->create(['name' => 'Team A']);
        $team2 = Team::factory()->create(['name' => 'Team B']);

        // Team A: Scored 5, Conceded 2 = GD +3
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 3,
            'away_score' => 1,
            'is_played' => true,
        ]);
        FootballMatch::factory()->create([
            'home_team_id' => $team2->id,
            'away_team_id' => $team1->id,
            'home_score' => 1,
            'away_score' => 2,
            'is_played' => true,
        ]);

        $standings = $this->service->getStandings();

        $team1Standing = collect($standings)->firstWhere('team.id', $team1->id);
        $this->assertEquals(5, $team1Standing['goals_for']);
        $this->assertEquals(2, $team1Standing['goals_against']);
        $this->assertEquals(3, $team1Standing['goal_difference']);
    }

    /**
     * Test that standings are sorted by points, then goal difference.
     */
    public function test_sorts_by_points_then_goal_difference()
    {
        $team1 = Team::factory()->create(['name' => 'Team A']);
        $team2 = Team::factory()->create(['name' => 'Team B']);
        $team3 = Team::factory()->create(['name' => 'Team C']);
        $team4 = Team::factory()->create(['name' => 'Team D']);

        // Team A: 6 points, GD +3
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team4->id,
            'home_score' => 3,
            'away_score' => 0,
            'is_played' => true,
        ]);
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team3->id,
            'home_score' => 2,
            'away_score' => 1,
            'is_played' => true,
        ]);

        // Team B: 6 points, GD +5 (should be 1st)
        FootballMatch::factory()->create([
            'home_team_id' => $team2->id,
            'away_team_id' => $team3->id,
            'home_score' => 4,
            'away_score' => 0,
            'is_played' => true,
        ]);
        FootballMatch::factory()->create([
            'home_team_id' => $team2->id,
            'away_team_id' => $team4->id,
            'home_score' => 3,
            'away_score' => 1,
            'is_played' => true,
        ]);

        $standings = $this->service->getStandings();

        $this->assertEquals($team2->id, $standings[0]['team']['id'], 'Team B should be 1st (same points, better GD)');
        $this->assertEquals($team1->id, $standings[1]['team']['id'], 'Team A should be 2nd');
        $this->assertEquals(1, $standings[0]['position']);
        $this->assertEquals(2, $standings[1]['position']);
    }

    /**
     * Test that unplayed matches are ignored.
     */
    public function test_ignores_unplayed_matches()
    {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        // Played match
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 1,
            'is_played' => true,
        ]);

        // Unplayed match (should be ignored)
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => null,
            'away_score' => null,
            'is_played' => false,
        ]);

        $standings = $this->service->getStandings();

        $team1Standing = collect($standings)->firstWhere('team.id', $team1->id);
        $this->assertEquals(1, $team1Standing['played'], 'Should only count played matches');
        $this->assertEquals(3, $team1Standing['points']);
    }

    /**
     * Test that teams with no matches have zero stats.
     */
    public function test_handles_teams_with_no_matches()
    {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        // Only team1 has a match
        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => Team::factory()->create()->id,
            'home_score' => 2,
            'away_score' => 0,
            'is_played' => true,
        ]);

        $standings = $this->service->getStandings();

        $team2Standing = collect($standings)->firstWhere('team.id', $team2->id);
        $this->assertEquals(0, $team2Standing['played']);
        $this->assertEquals(0, $team2Standing['points']);
        $this->assertEquals(0, $team2Standing['goals_for']);
        $this->assertEquals(0, $team2Standing['goal_difference']);
    }

    /**
     * Test position numbering is correct.
     */
    public function test_position_numbering_is_sequential()
    {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();
        $team3 = Team::factory()->create();

        FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_score' => 2,
            'away_score' => 0,
            'is_played' => true,
        ]);

        FootballMatch::factory()->create([
            'home_team_id' => $team3->id,
            'away_team_id' => $team1->id,
            'home_score' => 1,
            'away_score' => 1,
            'is_played' => true,
        ]);

        $standings = $this->service->getStandings();

        $positions = array_column($standings, 'position');
        $this->assertEquals([1, 2, 3], $positions, 'Positions should be 1, 2, 3');
    }
}
