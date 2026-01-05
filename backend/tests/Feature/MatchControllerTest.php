<?php

namespace Tests\Feature;

use App\Models\FootballMatch;
use App\Models\LeagueState;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LeagueState::create(['current_week' => 0]);
    }

    /**
     * Test updating a match score.
     */
    public function test_can_update_match_score()
    {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $match = FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'is_played' => false,
        ]);

        $response = $this->putJson("/api/v1/matches/{$match->id}", [
            'home_score' => 3,
            'away_score' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'home_team_id',
                'away_team_id',
                'home_score',
                'away_score',
                'is_played',
                'week',
                'home_team',
                'away_team',
            ]);

        $match->refresh();
        $this->assertEquals(3, $match->home_score);
        $this->assertEquals(1, $match->away_score);
        $this->assertTrue($match->is_played);
    }

    /**
     * Test updating non-existent match returns 404.
     */
    public function test_update_non_existent_match_returns_404()
    {
        $response = $this->putJson('/api/v1/matches/9999', [
            'home_score' => 2,
            'away_score' => 2,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Match not found',
            ]);
    }

    /**
     * Test validation for negative scores.
     */
    public function test_cannot_update_with_negative_score()
    {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $match = FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
        ]);

        $response = $this->putJson("/api/v1/matches/{$match->id}", [
            'home_score' => -1,
            'away_score' => 2,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test validation for missing scores.
     */
    public function test_cannot_update_with_missing_scores()
    {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $match = FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
        ]);

        $response = $this->putJson("/api/v1/matches/{$match->id}", [
            'home_score' => 2,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test updating already played match.
     */
    public function test_can_update_already_played_match()
    {
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $match = FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'is_played' => true,
            'home_score' => 1,
            'away_score' => 0,
        ]);

        $response = $this->putJson("/api/v1/matches/{$match->id}", [
            'home_score' => 2,
            'away_score' => 2,
        ]);

        $response->assertStatus(200);

        $match->refresh();
        $this->assertEquals(2, $match->home_score);
        $this->assertEquals(2, $match->away_score);
    }

    /**
     * Test updated match includes team data.
     */
    public function test_updated_match_includes_team_data()
    {
        $team1 = Team::factory()->create(['name' => 'Team A']);
        $team2 = Team::factory()->create(['name' => 'Team B']);

        $match = FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
        ]);

        $response = $this->putJson("/api/v1/matches/{$match->id}", [
            'home_score' => 1,
            'away_score' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('home_team.name', 'Team A')
            ->assertJsonPath('away_team.name', 'Team B');
    }
}
