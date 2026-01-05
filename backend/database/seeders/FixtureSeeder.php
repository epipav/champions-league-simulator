<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\FixtureGeneratorService;
use Illuminate\Database\Seeder;

class FixtureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Generate all fixtures for the 4 teams using FixtureGeneratorService
     *
     * @return void
     */
    public function run()
    {
        // Get all team IDs
        $teamIds = Team::pluck('id')->toArray();

        if (count($teamIds) !== 4) {
            $this->command->error('Exactly 4 teams are required. Please run TeamSeeder first.');

            return;
        }

        // Generate fixtures using the service
        $fixtureGenerator = new FixtureGeneratorService;
        $fixtures = $fixtureGenerator->generateFixtures($teamIds);

        // Insert fixtures into database
        foreach ($fixtures as $fixture) {
            FootballMatch::create($fixture);
        }

        $this->command->info('Fixtures generated successfully! Total: '.count($fixtures).' matches');
    }
}
