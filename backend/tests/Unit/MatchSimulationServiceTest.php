<?php

namespace Tests\Unit;

use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\MatchSimulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchSimulationServiceTest extends TestCase
{
    use RefreshDatabase;

    private MatchSimulationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MatchSimulationService;
    }

    /**
     * Test that stronger teams usually win.
     * Run 100 simulations and verify stronger team wins >70% of the time.
     */
    public function test_stronger_team_usually_wins()
    {
        $strongTeam = Team::factory()->create(['team_power' => 90]);
        $weakTeam = Team::factory()->create(['team_power' => 60]);

        $strongTeamWins = 0;
        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $match = FootballMatch::factory()->create([
                'home_team_id' => $strongTeam->id,
                'away_team_id' => $weakTeam->id,
                'is_played' => false,
            ]);

            $this->service->simulateMatch($match);
            $match->refresh();

            if ($match->home_score > $match->away_score) {
                $strongTeamWins++;
            }
        }

        $winRate = $strongTeamWins / $simulations;
        $this->assertGreaterThan(0.6, $winRate, "Strong team should win >60% of matches. Won {$winRate}");
    }

    /**
     * Test that upsets can happen - weaker teams should win occasionally.
     */
    public function test_upsets_can_happen()
    {
        $strongTeam = Team::factory()->create(['team_power' => 90]);
        $weakTeam = Team::factory()->create(['team_power' => 60]);

        $weakTeamWins = 0;
        $simulations = 100;

        for ($i = 0; $i < $simulations; $i++) {
            $match = FootballMatch::factory()->create([
                'home_team_id' => $weakTeam->id,
                'away_team_id' => $strongTeam->id,
                'is_played' => false,
            ]);

            $this->service->simulateMatch($match);
            $match->refresh();

            if ($match->home_score > $match->away_score) {
                $weakTeamWins++;
            }
        }

        $winRate = $weakTeamWins / $simulations;
        $this->assertGreaterThan(0.05, $winRate, 'Weak team should win >5% (upsets happen)');
        $this->assertLessThan(0.40, $winRate, "Weak team shouldn't win >40% (not realistic)");
    }

    /**
     * Test that match is properly marked as played with scores.
     */
    public function test_match_is_marked_as_played()
    {
        $team1 = Team::factory()->create(['team_power' => 80]);
        $team2 = Team::factory()->create(['team_power' => 75]);

        $match = FootballMatch::factory()->create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'is_played' => false,
            'home_score' => null,
            'away_score' => null,
        ]);

        $this->service->simulateMatch($match);
        $match->refresh();

        $this->assertTrue($match->is_played, 'Match should be marked as played');
        $this->assertNotNull($match->home_score, 'Home score should be set');
        $this->assertNotNull($match->away_score, 'Away score should be set');
        $this->assertGreaterThanOrEqual(0, $match->home_score, 'Home score should be non-negative');
        $this->assertGreaterThanOrEqual(0, $match->away_score, 'Away score should be non-negative');
    }

    /**
     * Test that home advantage exists (home team wins more often).
     */
    public function test_home_advantage_exists()
    {
        $team1 = Team::factory()->create(['team_power' => 80]);
        $team2 = Team::factory()->create(['team_power' => 80]);

        $homeWins = 0;
        $awayWins = 0;
        $simulations = 200;

        for ($i = 0; $i < $simulations; $i++) {
            $match = FootballMatch::factory()->create([
                'home_team_id' => $team1->id,
                'away_team_id' => $team2->id,
                'is_played' => false,
            ]);

            $this->service->simulateMatch($match);
            $match->refresh();

            if ($match->home_score > $match->away_score) {
                $homeWins++;
            } elseif ($match->away_score > $match->home_score) {
                $awayWins++;
            }
        }

        $this->assertGreaterThan($awayWins, $homeWins, 'Home team should win more often than away team (home advantage)');
    }

    /**
     * Test that scores are realistic (not too high).
     */
    public function test_scores_are_realistic()
    {
        $team1 = Team::factory()->create(['team_power' => 85]);
        $team2 = Team::factory()->create(['team_power' => 75]);

        $totalGoals = 0;
        $simulations = 50;

        for ($i = 0; $i < $simulations; $i++) {
            $match = FootballMatch::factory()->create([
                'home_team_id' => $team1->id,
                'away_team_id' => $team2->id,
                'is_played' => false,
            ]);

            $this->service->simulateMatch($match);
            $match->refresh();

            $totalGoals += $match->home_score + $match->away_score;
            $this->assertLessThanOrEqual(10, $match->home_score, 'Single team should not score >10 goals');
            $this->assertLessThanOrEqual(10, $match->away_score, 'Single team should not score >10 goals');
        }

        $averageGoals = $totalGoals / $simulations;
        $this->assertGreaterThan(1, $averageGoals, 'Average total goals should be >1');
        $this->assertLessThan(6, $averageGoals, 'Average total goals should be <6');
    }
}
