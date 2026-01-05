<?php

namespace Tests\Unit;

use App\Models\FootballMatch;
use App\Models\LeagueState;
use App\Models\Team;
use App\Services\PredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PredictionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PredictionService;
    }

    /**
     * Test predictions return null before week 4.
     */
    public function test_predictions_return_null_before_week_4()
    {
        // Create teams
        Team::factory()->count(4)->create();

        // Create league state at week 3
        LeagueState::create(['current_week' => 3]);

        $predictions = $this->service->getPredictions();

        $this->assertNull($predictions);
    }

    /**
     * Test predictions return null when league not initialized.
     */
    public function test_predictions_return_null_when_league_not_initialized()
    {
        Team::factory()->count(4)->create();

        $predictions = $this->service->getPredictions();

        $this->assertNull($predictions);
    }

    /**
     * Test predictions are available from week 4.
     */
    public function test_predictions_available_from_week_4()
    {
        // Create 4 teams with different powers
        $teams = [];
        $powers = [90, 85, 80, 75];
        foreach ($powers as $power) {
            $teams[] = Team::factory()->create(['team_power' => $power]);
        }

        // Create league state at week 4
        LeagueState::create(['current_week' => 4]);

        // Create and play matches for weeks 1-4
        $this->createAndPlayMatches($teams, 4);

        $predictions = $this->service->getPredictions();

        $this->assertNotNull($predictions);
        $this->assertCount(4, $predictions);
    }

    /**
     * Test predictions contain required fields.
     */
    public function test_predictions_contain_required_fields()
    {
        $teams = [];
        $powers = [90, 85, 80, 75];
        foreach ($powers as $power) {
            $teams[] = Team::factory()->create(['team_power' => $power]);
        }

        LeagueState::create(['current_week' => 4]);
        $this->createAndPlayMatches($teams, 4);

        $predictions = $this->service->getPredictions();

        foreach ($predictions as $prediction) {
            $this->assertArrayHasKey('team', $prediction);
            $this->assertArrayHasKey('probability', $prediction);
            $this->assertArrayHasKey('id', $prediction['team']);
            $this->assertArrayHasKey('name', $prediction['team']);
        }
    }

    /**
     * Test probabilities sum to approximately 100%.
     */
    public function test_probabilities_sum_to_100()
    {
        $teams = [];
        $powers = [90, 85, 80, 75];
        foreach ($powers as $power) {
            $teams[] = Team::factory()->create(['team_power' => $power]);
        }

        LeagueState::create(['current_week' => 4]);
        $this->createAndPlayMatches($teams, 4);

        $predictions = $this->service->getPredictions();

        $totalProbability = array_sum(array_column($predictions, 'probability'));

        // Allow for rounding errors (should be 99.9 to 100.1)
        $this->assertGreaterThanOrEqual(99, $totalProbability);
        $this->assertLessThanOrEqual(101, $totalProbability);
    }

    /**
     * Test stronger teams generally have higher probability.
     */
    public function test_stronger_teams_have_higher_probability_on_average()
    {
        // Run multiple times to account for randomness
        $strongerTeamHigherCount = 0;
        $iterations = 20;

        for ($i = 0; $i < $iterations; $i++) {
            // Refresh database for each iteration
            FootballMatch::query()->delete();
            Team::query()->delete();
            LeagueState::query()->delete();

            // Create teams - strong vs weak with larger power difference
            $strongTeam = Team::factory()->create(['team_power' => 95, 'name' => 'Strong']);
            $weakTeam = Team::factory()->create(['team_power' => 55, 'name' => 'Weak']);
            $mid1 = Team::factory()->create(['team_power' => 75, 'name' => 'Mid1']);
            $mid2 = Team::factory()->create(['team_power' => 70, 'name' => 'Mid2']);

            LeagueState::create(['current_week' => 4]);
            $this->createAndPlayMatches([$strongTeam, $weakTeam, $mid1, $mid2], 4);

            $predictions = $this->service->getPredictions();

            $strongProb = 0;
            $weakProb = 0;
            foreach ($predictions as $pred) {
                if ($pred['team']['name'] === 'Strong') {
                    $strongProb = $pred['probability'];
                }
                if ($pred['team']['name'] === 'Weak') {
                    $weakProb = $pred['probability'];
                }
            }

            if ($strongProb > $weakProb) {
                $strongerTeamHigherCount++;
            }
        }

        // Strong team should have higher probability at least 70% of the time (14 out of 20)
        $this->assertGreaterThanOrEqual(14, $strongerTeamHigherCount);
    }

    /**
     * Test predictions are sorted by probability descending.
     */
    public function test_predictions_sorted_by_probability_descending()
    {
        $teams = [];
        $powers = [90, 85, 80, 75];
        foreach ($powers as $power) {
            $teams[] = Team::factory()->create(['team_power' => $power]);
        }

        LeagueState::create(['current_week' => 4]);
        $this->createAndPlayMatches($teams, 4);

        $predictions = $this->service->getPredictions();

        $probabilities = array_column($predictions, 'probability');
        $sortedProbabilities = $probabilities;
        rsort($sortedProbabilities);

        $this->assertEquals($sortedProbabilities, $probabilities);
    }

    /**
     * Helper to create and play matches.
     */
    private function createAndPlayMatches(array $teams, int $weeks): void
    {
        $week = 1;
        $matchCount = 0;

        // Create round-robin matches
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                if ($week <= $weeks) {
                    FootballMatch::factory()->create([
                        'home_team_id' => $teams[$i]->id,
                        'away_team_id' => $teams[$j]->id,
                        'week' => $week,
                        'is_played' => true,
                        'home_score' => rand(0, 3),
                        'away_score' => rand(0, 3),
                    ]);
                    $matchCount++;
                    if ($matchCount % 2 === 0) {
                        $week++;
                    }
                }
            }
        }

        // Create remaining unplayed matches
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                FootballMatch::factory()->create([
                    'home_team_id' => $teams[$j]->id,
                    'away_team_id' => $teams[$i]->id,
                    'week' => $week,
                    'is_played' => false,
                ]);
                $matchCount++;
                if ($matchCount % 2 === 0) {
                    $week++;
                }
            }
        }
    }
}
